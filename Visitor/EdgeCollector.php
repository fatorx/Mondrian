<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use Trismegiste\Mondrian\Graph;

/**
 * EdgeCollector is a visitor to transform code into graph edges
 */
class EdgeCollector extends \PHPParser_NodeVisitor_NameResolver
{

    protected $currentClass = false;
    protected $currentClassVertex = null;
    protected $currentMethod = false;
    protected $currentMethodNode = null;
    protected $currentMethodParamOrder;
    protected $graph;
    protected $vertex;
    protected $inheritanceMap;

    public function __construct(Graph\Graph $g, array &$v, array &$map)
    {
        $this->graph = $g;
        $this->vertex = &$v;
        $this->inheritanceMap = &$map;
    }

    public function enterNode(\PHPParser_Node $node)
    {
        parent::enterNode($node);

        switch ($node->getType()) {

            case 'Stmt_Class' :
                $this->enterClassNode($node);
                break;

            case 'Stmt_Interface' :
                $this->enterInterfaceNode($node);
                break;

            case 'Stmt_ClassMethod' :
                if ($node->isPublic()) {
                    $this->enterMethodNode($node);
                }
                break;

            case 'Param' :
                if ($this->currentMethod && $this->currentMethodNode->isPublic()) {
                    $this->enterParamNode($node);
                }
                break;

            case 'Expr_MethodCall' :
                /* if (false !== array_search($node->name, $this->callFilter)) {
                  $this->analyze[$this->currentClass]['calling'][] =
                  array('in' => $this->currentMethod,
                  'method' => $node->name);
                  } */
                break;

            case 'Expr_New':
                break;
        }
    }

    public function leaveNode(\PHPParser_Node $node)
    {
        if ($node->getType() == 'Stmt_Class') {
            $this->currentClass = false;
            $this->currentClassVertex = null;
        }
        if ($node->getType() == 'Stmt_Interface') {
            $this->currentClass = false;
            $this->currentClassVertex = null;
        }
        if ($node->getType() == 'Stmt_ClassMethod') {
            $this->currentMethod = false;
            $this->currentMethodNode = null;
        }
    }

    protected function findVertex($type, $key)
    {
        if (array_key_exists($key, $this->vertex[$type])) {
            return $this->vertex[$type][$key];
        }
        return null;
    }

    protected function findParamVertex($className, $methodName, $paramName)
    {
        if (array_key_exists($paramName, $this->currentMethodParamOrder)) {
            $order = $this->currentMethodParamOrder[$paramName];
            return $this->findVertex('param', $className . '::' . $methodName . '/' . $order);
        }
        return null;
    }

    protected function getDeclaringClass($cls, $meth)
    {
        return $this->inheritanceMap[$cls]['method'][$meth];
    }

    protected function isInterface($cls)
    {
        return $this->inheritanceMap[$cls]['interface'];
    }

    protected function enterParamNode(\PHPParser_Node_Param $node)
    {
        // one link from the method if we are in the declaring class
        // and one link if the type (class/interface) exists in the graph
        $declaringClass = $this->getDeclaringClass($this->currentClass, $this->currentMethod);
        $paramVertex = $this->findParamVertex($declaringClass, $this->currentMethod, $node->name);
        if (!is_null($paramVertex)) {
            if ($this->currentClass == $declaringClass) {
                // we are in the declaring class of this method for this param
                // we search the vertex of the method signature
                $signatureVertex = $this->findVertex('method', $declaringClass . '::' . $this->currentMethod);
                $this->graph->addEdge($signatureVertex, $paramVertex);
                // now the type of the param
                $paramType = (string) $node->type;
                if (strlen($paramType) > 0) {
                    // there is a type, we add a link to the type, if it is found
                    // first we search in class
                    $typeVertex = $this->findVertex('class', $paramType);
                    if (is_null($typeVertex)) {
                        // if not, in interface
                        $typeVertex = $this->findVertex('interface', $paramType);
                        if (!is_null($typeVertex)) {
                            // we add the edge
                            $this->graph->addEdge($paramVertex, $typeVertex);
                        }
                    }
                }
            }
            // one link from the impl to the param
            if (!$this->isInterface($this->currentClass) && !$this->currentMethodNode->isAbstract()) {
                $impl = $this->findVertex('impl', $this->currentClass . '::' . $this->currentMethod);
                $this->graph->addEdge($impl, $paramVertex);
            }
        }
    }

    protected function enterMethodNode(\PHPParser_Node_Stmt_ClassMethod $node)
    {
        $this->currentMethod = $node->name;
        $this->currentMethodNode = $node;
        // we store the param order of the current method
        $this->currentMethodParamOrder = array();
        foreach ($node->params as $order => $aParam) {
            $this->currentMethodParamOrder[$aParam->name] = $order;
        }
        // search for the declaring class of this method
        $declaringClass = $this->getDeclaringClass($this->currentClass, $this->currentMethod);
        $signature = $this->findVertex('method', $declaringClass . '::' . $node->name);
        $src = $this->currentClassVertex;
        // if current class == declaring class, we add the edge
        if ($declaringClass == $this->currentClass) {
            $this->graph->addEdge($src, $signature);
        }
        // if not abstract, the implementation depends on the class
        // for odd reason, a method in an interface is not abstract
        // that's why, there is a double check
        if (!$this->isInterface($this->currentClass) && !$node->isAbstract()) {
            $impl = $this->findVertex('impl', $this->currentClass . '::' . $node->name);
            $this->graph->addEdge($impl, $src);
            // who is embedding the impl ?
            if ($declaringClass == $this->currentClass) {
                $this->graph->addEdge($signature, $impl);
            } else {
                $this->graph->addEdge($src, $impl);
            }
        }
    }

    protected function enterInterfaceNode(\PHPParser_Node_Stmt_Interface $node)
    {
        $this->currentClass = (string) $node->namespacedName;
        $src = $this->vertex['interface'][$this->currentClass];
        $this->currentClassVertex = $src;

        // implements
        foreach ($node->extends as $interf) {
            if (null !== $dst = $this->findVertex('interface', (string) $interf)) {
                $this->graph->addEdge($src, $dst);
            }
        }
    }

    protected function enterClassNode(\PHPParser_Node_Stmt_Class $node)
    {
        $this->currentClass = (string) $node->namespacedName;
        $src = $this->vertex['class'][$this->currentClass];
        $this->currentClassVertex = $src;

        // extends
        if (!is_null($node->extends)) {
            if (null !== $dst = $this->findVertex('class', (string) $node->extends)) {
                $this->graph->addEdge($src, $dst);
            }
        }
        // implements
        foreach ($node->implements as $interf) {
            if (null !== $dst = $this->findVertex('interface', (string) $interf)) {
                $this->graph->addEdge($src, $dst);
            }
        }
    }

}