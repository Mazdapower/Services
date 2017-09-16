<?php

namespace AppBundle\EventListener\Submission\Delegation;

use AppBundle\Entity\Submission;
use AppBundle\Entity\Scenario;
use Ds\Component\Camunda\Model\Variable;
use Ds\Component\Camunda\Query\ProcessDefinitionParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BpmListener
 */
class BpmListener
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Ds\Component\Api\Api\Factory
     */
    protected $factory;

    /**
     * @var \Ds\Component\Config\Service\ConfigService
     */
    protected $configService;

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Forward the submission to the bpm service
     *
     * @param \AppBundle\Entity\Submission $submission
     */
    public function postPersist(Submission $submission)
    {
        // Circular reference error workaround
        // @todo Look into fixing this
        $this->factory = $this->container->get('ds_api.factory');
        $this->configService = $this->container->get('ds_config.service.config');
        //

        $scenario = $submission->getScenario();

        if (Scenario::TYPE_BPM !== $scenario->getType()) {
            return;
        }

        $api = $this->factory->create();
//        $parameters = new ProcessDefinitionParameters;
//        $parameters->setKey($scenario->getData('process_definition_key'));
//        $xml = $api->camunda->processDefinition->getXml(null, $parameters);
        $service = $scenario->getService();
        $parameters = new ProcessDefinitionParameters;
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.api_url'), ''));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.api_user'), ''));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.api_key'), ''));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.service_uuid'), $service->getUuid()));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.scenario_uuid'), $scenario->getUuid()));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.identity'), $submission->getIdentity()));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.identity_uuid'), $submission->getIdentityUuid()));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.submission_uuid'), $submission->getUuid()));
        $parameters->addVariable(new Variable($this->configService->get('app.bpm.variables.none_start_event_form_data'), $submission->getData(), Variable::TYPE_JSON));
        $parameters->setKey($scenario->getData('process_definition_key'));
        $api->camunda->processDefinition->start(null, $parameters);
    }
}
