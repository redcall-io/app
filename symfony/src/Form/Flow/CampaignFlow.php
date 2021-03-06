<?php

namespace App\Form\Flow;

use App\Enum\Type;
use App\Form\Model\Campaign;
use App\Form\Type\CampaignType;
use App\Form\Type\ChooseCampaignOperationChoicesType;
use App\Form\Type\CreateCampaignOperationType;
use App\Form\Type\CreateOrUseOperationType;
use App\Form\Type\UseCampaignOperationType;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

class CampaignFlow extends FormFlow
{
    /**
     * @var Type
     */
    private $type;

    public function getType() : Type
    {
        return $this->type;
    }

    public function setType(Type $type) : CampaignFlow
    {
        $this->type = $type;

        return $this;
    }

    protected function loadStepsConfig()
    {
        return [
            1 => [
                'form_type'    => CampaignType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                    'type'              => $this->getType(),
                ],
            ],
            2 => [
                'form_type'    => CreateOrUseOperationType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var Campaign $data */
                    $data = $flow->getFormData();

                    return !$data->hasOperation;
                },
            ],
            3 => [
                'form_type'    => CreateCampaignOperationType::class,
                'form_options' => [
                    'validation_groups' => ['Create'],
                ],
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var Campaign $data */
                    $data = $flow->getFormData();

                    return !$data->hasOperation || Campaign::CREATE_OPERATION !== $data->createOperation;
                },
            ],
            4 => [
                'form_type'    => UseCampaignOperationType::class,
                'form_options' => [
                    'validation_groups' => ['Use'],
                ],
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var Campaign $data */
                    $data = $flow->getFormData();

                    return !$data->hasOperation || Campaign::USE_OPERATION !== $data->createOperation;
                },
            ],
            5 => [
                'form_type'    => ChooseCampaignOperationChoicesType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var Campaign $data */
                    $data = $flow->getFormData();

                    return !$data->hasOperation || 0 === count($data->trigger->getAnswers());
                },
            ],
        ];
    }
}