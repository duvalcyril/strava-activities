const echarts = require('echarts');
const fs = require('fs');
const currentDir = process.cwd();

const chart = echarts.init(null, null, {
    renderer: 'svg',
    ssr: true,
    width: 1000,
    height: 300
});

const data = fs.readFileSync(currentDir + '/build/chart.json');
const option = {
    animation: false,
    color: ['#E34902'],
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true,
    },
    xAxis: [
        {
            type: 'time',
            boundaryGap: false,
            axisTick: {
              show: false
            },
            axisLabel: {
                formatter: {
                    year: '{yyyy}',
                    month: '{MMM} {yyyy}',
                    day: '{d}',
                    hour: '{HH}:{mm}',
                    minute: '{HH}:{mm}',
                    second: '{HH}:{mm}:{ss}',
                    millisecond: '{hh}:{mm}:{ss} {SSS}',
                    none: '{yyyy}-{MM}-{dd} {hh}:{mm}:{ss} {SSS}'
                }
            },
            splitLine: {
                show: true,
                lineStyle: {
                    color: '#E0E6F1'
                }
            }
        }
    ],
    yAxis: [
        {
            type: 'value',
            splitLine: {
                show: false
            },
            max: function (value) {
                return Math.ceil(value.max / 10) * 10;
            },
            axisLabel: {
                formatter: '{value} km'
            }
        }
    ],
    series: [
        {
            name: 'Average distance / week',
            type: 'line',
            smooth: false,
            label: {
                show: true,
                formatter: function (dataset) {
                    if(dataset.dataIndex % 2 !== 0){
                       // return '';
                    }
                    return `${dataset.data[1]} km`;
                },
                rotate: -45,
            },
            lineStyle: {
                width: 1,
            },
            symbolSize: 6,
            showSymbol: true,
            areaStyle: {
                opacity: 0.8,
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    {
                        offset: 0,
                        color: 'rgba(227, 73, 2, 0.3)'
                    },
                    {
                        offset: 0.6,
                        color: 'rgba(227, 73, 2, 0.3)'
                    },
                    {
                        offset: 1,
                        color: 'rgba(227, 73, 2, 0)'
                    }
                ])
            },
            markLine: {
                data: [
                    [
                        {
                            symbol: 'none',
                            x: '90%',
                            yAxis: 'max'
                        },
                        {
                            symbol: 'circle',
                            label: {
                                position: 'start',
                                formatter: 'Max'
                            },
                            type: 'max'
                        }
                    ]
                ]
            },
            emphasis: {
                focus: 'series'
            },
            data: JSON.parse(data),
        }
    ]
};

chart.setOption(option);

fs.writeFileSync(currentDir +'/build/chart.svg', chart.renderToSVGString());
process.exit(0);
