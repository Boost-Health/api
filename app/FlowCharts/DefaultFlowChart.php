<?php

namespace App\FlowCharts;

use App\Objects\FlowChartNextObject;

final class DefaultFlowChart extends BaseFlowChart
{
    public function init(): FlowChartNextObject
    {
        return new FlowChartNextObject(null, ['You have reached the Boost Health Bot']);
    }
}
