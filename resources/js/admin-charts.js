// Admin dashboard charts (resources/views/admin.blade.php). Loaded by
// resources/js/app.js only on pages that have [data-admin-charts].
//
// Replaces the full ~1 MB echarts CDN build and three inline <script>
// blocks. Only the chart types and components actually drawn are
// imported, so the bundle carries a fraction of the library. The inline
// version also ran on every admin tab (throwing on the missing chart
// elements outside the dashboard) and reused one `chart` variable for two
// charts, so resizing the window resized the wrong one.
import * as echarts from 'echarts/core';
import { BarChart, LineChart, PieChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TitleComponent, TooltipComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([BarChart, LineChart, PieChart, GridComponent, LegendComponent, TitleComponent, TooltipComponent, CanvasRenderer]);

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const titleStyle = { color: '#1F2937', fontSize: 13 };

function monthlyOption(data) {
    return {
        title: { text: 'Monthly Upload Trends', left: 'left', textStyle: titleStyle },
        tooltip: { trigger: 'axis' },
        legend: { data: ['Uploads', 'Approvals'], bottom: 10, left: 'center' },
        xAxis: { type: 'category', data: MONTHS },
        yAxis: { type: 'value' },
        series: [
            { name: 'Uploads', type: 'bar', itemStyle: { color: '#1E3A8A' }, data: data.uploads },
            { name: 'Approvals', type: 'bar', itemStyle: { color: '#10B981' }, data: data.approvals },
        ],
    };
}

function statusOption(data) {
    return {
        title: { text: 'Research Status Distribution', left: 'left', textStyle: titleStyle },
        tooltip: { trigger: 'item' },
        legend: { bottom: '10', left: 'center' },
        series: [
            {
                name: 'Research Status',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                label: { show: false, position: 'center' },
                emphasis: { label: { show: true, fontSize: 24, fontWeight: 'bold' } },
                labelLine: { show: false },
                data: [
                    { value: data.status.approved, name: 'Approved', itemStyle: { color: '#198754' } },
                    { value: data.status.pending, name: 'Pending', itemStyle: { color: '#FFC107' } },
                    { value: data.status.rejected, name: 'Rejected', itemStyle: { color: '#DC3545' } },
                ],
            },
        ],
    };
}

function engagementOption(data) {
    const line = (name, color, values) => ({
        name,
        type: 'line',
        smooth: true,
        data: values,
        lineStyle: { color, width: 3 },
        itemStyle: { color: '#FFFFFF', borderColor: color, borderWidth: 3 },
        symbol: 'circle',
        symbolSize: 8,
    });

    return {
        title: { text: 'Downloads & Engagement Trends (Views vs Downloads)', left: 'left', textStyle: titleStyle },
        tooltip: { trigger: 'axis' },
        legend: { bottom: 10, left: 'center', data: ['Views', 'Downloads'] },
        grid: { top: 70, left: '8%', right: '5%', bottom: 60, containLabel: true },
        xAxis: { type: 'category', boundaryGap: false, data: MONTHS },
        yAxis: { type: 'value' },
        series: [line('Views', '#7C4DFF', data.views), line('Downloads', '#10B981', data.downloads)],
    };
}

const charts = [
    { id: 'monthlyChart', option: monthlyOption },
    { id: 'pieChart', option: statusOption },
    { id: 'lineChart', option: engagementOption },
];

let instances = [];

function resizeAll() {
    instances.forEach((chart) => chart.resize());
}

// Turbo snapshots the page when leaving it; dispose first so the cached
// copy isn't holding live canvases and the resize listener doesn't leak
// into every page visited afterwards.
function disposeAll() {
    instances.forEach((chart) => chart.dispose());
    instances = [];
    window.removeEventListener('resize', resizeAll);
}

document.addEventListener('turbo:before-cache', disposeAll);

export function init() {
    const source = document.querySelector('[data-admin-charts]');
    if (!source) return;

    disposeAll();

    let data;
    try {
        data = JSON.parse(source.textContent);
    } catch (error) {
        console.error('[admin-charts] invalid chart data', error);
        return;
    }

    for (const { id, option } of charts) {
        const el = document.getElementById(id);
        if (!el) continue;

        const chart = echarts.init(el);
        chart.setOption(option(data));
        instances.push(chart);
    }

    window.addEventListener('resize', resizeAll);
}
