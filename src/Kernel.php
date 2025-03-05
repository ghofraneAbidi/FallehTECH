<?php
namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    protected function buildContainer(): ContainerBuilder
{
    $container = parent::buildContainer();
    $container->setParameter('gemini.api_key', 'your_real_api_key_here');
    return $container;
}
}
