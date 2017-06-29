<?php

namespace Sonata\NotificationBundle\DependencyInjection\Compiler;

use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Salma Chakroun <chakroun.salma@gmail.com>
 */
class NotificationMappingCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $modelDir = realpath(__DIR__.'/../../Resources/config/doctrine');
        $mappings = array(
            $modelDir => 'Sonata\NotificationBundle\Document',
        );

        $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, array('doctrine_mongodb.odm.default_document_manager')));
    }

}
