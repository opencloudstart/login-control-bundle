<?php
declare(strict_types=1);

namespace OCS\LoginControlBundle;

use LoginControl\src\DependencyInjection\LoginControlExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OCSLoginControlBundle extends Bundle
{
    protected function getContainerExtensionClass(): string
    {
        return LoginControlExtension::class;
    }

}
