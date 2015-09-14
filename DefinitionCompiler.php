<?php

namespace Innmind\Rest\Server;

class DefinitionCompiler
{
    protected $passes = [];

    /**
     * Add a compiler pass
     *
     * @param CompilerPassInterface $pass
     *
     * @return DefinitionCompiler self
     */
    public function addCompilerPass(CompilerPassInterface $pass)
    {
        $this->passes[] = $pass;

        return $this;
    }

    /**
     * Process the whole definition registry
     *
     * @param Registry $registry
     *
     * @return DefinitionCompiler self
     */
    public function process(Registry $registry)
    {
        foreach ($this->passes as $pass) {
            $pass->process($registry);
        }

        return $this;
    }
}
