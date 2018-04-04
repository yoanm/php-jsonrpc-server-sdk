<?php
namespace Yoanm\JsonRpcServer\Infra\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Resolver\ArrayMethodResolver;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * Class JsonRpcServerExtension
 */
class JsonRpcServerExtension extends Extension
{
    // Use this service to inject string request
    const ENDPOINT_SERVICE_NAME = 'yoanm.jsonrpc_server_sdk.endpoint';
    // Use this tag to inject your own resolver
    const METHOD_RESOLVER_TAG = 'yoanm.jsonrpc_server_sdk.method_resolver';
    // Use this tag to inject your JSON-RPC methods into the default method resolver
    const JSONRPC_METHOD_TAG = 'yoanm.jsonrpc_server_sdk.jsonrpc_method';
    // In case you use the ArrayMethodResolver, use this service to manually inject your JSON-RPC methods
    const DEFAULT_METHOD_RESOLVER_SERVICE_NAME   = 'yoanm.jsonrpc_server_sdk.infra.resolver.array_method_resolver';


    const JSONRPC_METHOD_TAG_METHOD_NAME_KEY = 'method';


    private $appResponseCreatorServiceId        = 'yoanm.jsonrpc_server_sdk.app.creator.response';
    private $appCustomExceptionCreatorServiceId = 'yoanm.jsonrpc_server_sdk.app.creator.custom_exception';
    private $appRequestDenormalizerServiceId    = 'yoanm.jsonrpc_server_sdk.app.serialization.request_denormalizer';
    private $appResponseNormalizerServiceId     = 'yoanm.jsonrpc_server_sdk.app.serialization.response_normalizer';
    private $appMethodManagerServiceId          = 'yoanm.jsonrpc_server_sdk.app.manager.method';
    private $appRequestHandlerServiceId         = 'yoanm.jsonrpc_server_sdk.app.handler.request';

    private $infraRawReqSerializerServiceId  = 'yoanm.jsonrpc_server_sdk.infra.serialization.raw_request_serializer';
    private $infraRawRespSerializerServiceId = 'yoanm.jsonrpc_server_sdk.infra.serialization.raw_response_serializer';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Use only references to avoid class instantiation
        // And don't use file configuration in order to not add Symfony\Component\Config as dependency
        $this->createPublicServiceDefinitions($container);
        $this->createInfraServiceDefinitions($container);
        $this->createAppServiceDefinitions($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createAppServiceDefinitions(ContainerBuilder $container)
    {
        // RequestDenormalizer
        $container->setDefinition($this->appRequestDenormalizerServiceId, new Definition(RequestDenormalizer::class));
        // ResponseNormalizer
        $container->setDefinition($this->appResponseNormalizerServiceId, new Definition(ResponseNormalizer::class));
        // ResponseCreator
        $container->setDefinition($this->appResponseCreatorServiceId, new Definition(ResponseCreator::class));
        // CustomExceptionCreator
        $container->setDefinition(
            $this->appCustomExceptionCreatorServiceId,
            new Definition(CustomExceptionCreator::class)
        );

        // MethodManager
        $container->setDefinition(
            $this->appMethodManagerServiceId,
            new Definition(
                MethodManager::class,
                [
                    new Reference($this->getMethodResolverServiceId($container)),
                    new Reference($this->appCustomExceptionCreatorServiceId)
                ]
            )
        );
        // RequestHandler
        $container->setDefinition(
            $this->appRequestHandlerServiceId,
            new Definition(
                RequestHandler::class,
                [
                    new Reference($this->appMethodManagerServiceId),
                    new Reference($this->appResponseCreatorServiceId)
                ]
            )
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createInfraServiceDefinitions(ContainerBuilder $container)
    {
        // RawRequestSerializer
        $container->setDefinition(
            $this->infraRawReqSerializerServiceId,
            new Definition(
                RawRequestSerializer::class,
                [new Reference($this->appRequestDenormalizerServiceId)]
            )
        );

        // RawResponseSerializer
        $container->setDefinition(
            $this->infraRawRespSerializerServiceId,
            new Definition(
                RawResponseSerializer::class,
                [new Reference($this->appResponseNormalizerServiceId)]
            )
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function createPublicServiceDefinitions(ContainerBuilder $container)
    {
        $container->setDefinition(
            self::ENDPOINT_SERVICE_NAME,
            (new Definition(
                JsonRpcEndpoint::class,
                [
                    new Reference($this->infraRawReqSerializerServiceId),
                    new Reference($this->appRequestHandlerServiceId),
                    new Reference($this->infraRawRespSerializerServiceId),
                    new Reference($this->appResponseCreatorServiceId)
                ]
            ))->setPrivate(false)
        );

        $container->setDefinition(
            self::DEFAULT_METHOD_RESOLVER_SERVICE_NAME,
            (new Definition(ArrayMethodResolver::class))->setPrivate(false)
        );
    }

    /**
     * @param ContainerBuilder $container
     */
    private function getMethodResolverServiceId(ContainerBuilder $container)
    {
        $serviceIdList = array_keys($container->findTaggedServiceIds(self::METHOD_RESOLVER_TAG));
        $serviceCount = count($serviceIdList);
        if ($serviceCount > 0) {
            if ($serviceCount > 1) {
                throw new LogicException(
                    sprintf(
                        'Only one method resolver could be defined, found following services : %s',
                        implode(', ', $serviceIdList)
                    )
                );
            }
            // Use the first result
            $resolverServiceId = array_shift($serviceIdList);
        } else {
            // Use ArrayMethodResolver as default resolver
            $resolverServiceId = self::DEFAULT_METHOD_RESOLVER_SERVICE_NAME;
            $this->loadJsonRpcMethodsFromTag($container);
        }

        return $resolverServiceId;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadJsonRpcMethodsFromTag(ContainerBuilder $container)
    {
        // Check if methods have been defined by tags
        $methodServiceList = $container->findTaggedServiceIds(self::JSONRPC_METHOD_TAG);
        $defaultResolverDefinition = $container->getDefinition(self::DEFAULT_METHOD_RESOLVER_SERVICE_NAME);

        foreach ($methodServiceList as $serviceId => $tags) {
            $firstTag = array_shift($tags);
            if (!is_array($firstTag) || !array_key_exists(self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY, $firstTag)) {
                throw new LogicException(sprintf(
                    'Service %s is taggued as JSON-RPC method but does not have'
                        .'method name defined under "%s" tag attribute key',
                    $serviceId,
                    self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY
                ));
            }
            $defaultResolverDefinition->addMethodCall(
                'addMethod',
                [
                    new Reference($serviceId),
                    $firstTag[self::JSONRPC_METHOD_TAG_METHOD_NAME_KEY]
                ]
            );
        }
    }
}
