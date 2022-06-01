<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodInvocation;

use SmartAssert\Compiler\CompilableSource\Model\StaticObject;

interface StaticObjectMethodInvocationInterface extends MethodInvocationInterface
{
    public function getStaticObject(): StaticObject;
}
