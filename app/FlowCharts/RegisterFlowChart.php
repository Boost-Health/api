<?php

namespace App\FlowCharts;

use App\Objects\FlowChartNextObject;

final class RegisterFlowChart extends BaseFlowChart
{
    public function init(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'gender',
            [
                sprintf(
                    "Hello %s! I'm here to help you manage your health and wellness. To get started, could you share some basic information about yourself? This will help me provide personalized advice. Please answer as much as you're comfortable with 😀",
                    $this->user->first_name
                ),
                'Lets start with your Gender. I do like to know if you are Male or Female?',
            ]
        );
    }

    protected function gender(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'age',
            [
                'Awesome. How about your age?',
            ],
            ['gender' => $this->message->body]
        );
    }

    protected function age(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'medicalConditions',
            [
                'Do you currently have any known medical conditions or allergies? You can reply me No if you do not have, otherwise please tell me about it',
            ],
            ['age' => $this->message->body]
        );
    }

    protected function medicalConditions(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'medications',
            [
                'Are you currently on any medications? If No, just reply me no, otherwise feel free to tell me about the medications, perhaps their names if you remember',
            ],
            ['medicalConditions' => $this->message->body]
        );
    }

    protected function medications(): FlowChartNextObject
    {
        return new FlowChartNextObject(
            'end',
            [
                'Do you have any lifestyle habits I should be aware of? Smoking? Frequency exercise? Sitting a lot? Give me an idea about your lifestyle',
            ],
            ['medications' => $this->message->body]
        );
    }

    protected function end(): FlowChartNextObject
    {
        $this->user->markAsOnboarded();

        return new FlowChartNextObject(
            null,
            [
                sprintf('Alright. That\'s about it for now. Thank you %s!', $this->user->first_name),
            ],
            ['lifestyle' => $this->message->body]
        );
    }
}
