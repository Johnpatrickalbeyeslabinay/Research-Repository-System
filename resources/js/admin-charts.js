// resources/js/admin-charts.js

import * as echarts from "echarts/core";

import { BarChart } from "echarts/charts";

import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
} from "echarts/components";

import { CanvasRenderer } from "echarts/renderers";

echarts.use([
    BarChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    CanvasRenderer,
]);

let researchChart = null;
let registeredUsersChart = null;
let searchKeywordsChart = null;
let searchRaceTimer = null;

export function init() {
    console.log("[admin-charts] INIT");

    const reportData = window.adminReportData ?? {};

    const researchData = reportData.research ?? {};

    const usersData = reportData.users ?? {};

    const searchRaceData = Array.isArray(reportData.searchRace)
        ? reportData.searchRace
        : [];

    console.log("[admin-charts] reportData:", reportData);
    console.log("[admin-charts] searchRace:", searchRaceData);

    // =====================================================
    // RESEARCH UPLOAD CHART
    // =====================================================

    const researchDom = document.getElementById("reportChart");

    if (researchDom) {
        const existing = echarts.getInstanceByDom(researchDom);

        if (existing) {
            existing.dispose();
        }

        researchChart = echarts.init(researchDom);

        researchChart.setOption({
            animation: true,

            tooltip: {
                trigger: "axis",

                axisPointer: {
                    type: "shadow",
                },
            },

            grid: {
                left: "3%",
                right: "3%",
                top: "8%",
                bottom: "10%",
                containLabel: true,
            },

            xAxis: {
                type: "category",
                data: researchData.labels ?? [],

                axisTick: {
                    alignWithLabel: true,
                },
            },

            yAxis: {
                type: "value",
                minInterval: 1,
            },

            series: [
                {
                    name: "Research Uploads",

                    type: "bar",

                    data: researchData.values ?? [],

                    barMaxWidth: 45,

                    itemStyle: {
                        borderRadius: [6, 6, 0, 0],
                    },
                },
            ],
        });
    }

    // =====================================================
    // REGISTERED USERS CHART
    // =====================================================

    const usersDom = document.getElementById("registeredUsersChart");

    if (usersDom) {
        const existing = echarts.getInstanceByDom(usersDom);

        if (existing) {
            existing.dispose();
        }

        registeredUsersChart = echarts.init(usersDom);

        registeredUsersChart.setOption({
            animation: true,

            tooltip: {
                trigger: "axis",

                axisPointer: {
                    type: "shadow",
                },
            },

            legend: {
                top: 0,
            },

            grid: {
                left: "3%",
                right: "3%",
                top: "12%",
                bottom: "10%",
                containLabel: true,
            },

            xAxis: {
                type: "category",
                data: usersData.labels ?? [],

                axisTick: {
                    alignWithLabel: true,
                },
            },

            yAxis: {
                type: "value",
                minInterval: 1,
            },

            series: [
                {
                    name: "Students",

                    type: "bar",

                    data: usersData.students ?? [],

                    barMaxWidth: 35,
                },

                {
                    name: "Instructors",

                    type: "bar",

                    data: usersData.instructors ?? [],

                    barMaxWidth: 35,
                },
            ],
        });
    }

    // =====================================================
    // SEARCH KEYWORDS BAR RACE
    // =====================================================

    const searchKeywordsDom = document.getElementById("searchKeywordsChart");

    if (searchKeywordsDom) {
        const existing = echarts.getInstanceByDom(searchKeywordsDom);

        if (existing) {
            existing.dispose();
        }

        searchKeywordsChart = echarts.init(searchKeywordsDom);

        // Stop previous animation.
        if (searchRaceTimer) {
            clearInterval(searchRaceTimer);
            searchRaceTimer = null;
        }

        if (searchRaceData.length === 0) {
            searchKeywordsChart.setOption({
                title: {
                    text: "No search data available",
                    left: "center",
                    top: "middle",
                },

                xAxis: {
                    show: false,
                },

                yAxis: {
                    show: false,
                },

                series: [],
            });
        } else {
            let frameIndex = 0;

            const renderSearchFrame = () => {
                const frame = searchRaceData[frameIndex] ?? {};

                const rows = Array.isArray(frame.data) ? [...frame.data] : [];

                rows.sort(
                    (a, b) => Number(b.value ?? 0) - Number(a.value ?? 0),
                );

                const topRows = rows.slice(0, 10);

                const names = topRows.map((item) => item.name ?? "").reverse();

                const values = topRows
                    .map((item) => Number(item.value ?? 0))
                    .reverse();

                searchKeywordsChart.setOption({
                    animationDuration: 800,

                    animationDurationUpdate: 800,

                    animationEasing: "cubicOut",

                    animationEasingUpdate: "cubicOut",

                    title: {
                        text: frame.label ?? "",
                        left: "center",
                    },

                    tooltip: {
                        trigger: "axis",

                        axisPointer: {
                            type: "shadow",
                        },

                        formatter(params) {
                            const item = params?.[0];

                            if (!item) {
                                return "";
                            }

                            return `
                                <strong>${item.name}</strong><br>
                                Searches: ${item.value}
                            `;
                        },
                    },

                    grid: {
                        left: "5%",
                        right: "8%",
                        top: "15%",
                        bottom: "5%",
                        containLabel: true,
                    },

                    xAxis: {
                        type: "value",

                        min: 0,

                        minInterval: 1,
                    },

                    yAxis: {
                        type: "category",

                        data: names,
                    },

                    series: [
                        {
                            name: "Searches",

                            type: "bar",

                            data: values,

                            barMaxWidth: 35,

                            label: {
                                show: true,

                                position: "right",
                            },

                            itemStyle: {
                                borderRadius: [0, 6, 6, 0],

                                color: (params) => {
                                    const colors = [
                                        "#2563eb", // Blue
                                        "#0d9488", // Teal
                                        "#f59e0b", // Amber
                                        "#8b5cf6", // Purple
                                        "#ef4444", // Red
                                        "#10b981", // Green
                                        "#ec4899", // Pink
                                        "#6366f1", // Indigo
                                        "#f97316", // Orange
                                        "#14b8a6", // Cyan
                                    ];

                                    return colors[
                                        params.dataIndex % colors.length
                                    ];
                                },
                            },
                        },
                    ],
                });
            };

            renderSearchFrame();

            if (searchRaceData.length > 1) {
                searchRaceTimer = setInterval(() => {
                    frameIndex++;

                    if (frameIndex >= searchRaceData.length) {
                        frameIndex = 0;
                    }

                    renderSearchFrame();
                }, 1800);
            }
        }
    }

    // =====================================================
    // RESIZE
    // =====================================================

    const resizeCharts = () => {
        researchChart?.resize();

        registeredUsersChart?.resize();

        searchKeywordsChart?.resize();
    };

    window.addEventListener("resize", resizeCharts);

    console.log("[admin-charts] FILE INITIALIZATION COMPLETE");

    window.printReport = function () {
        const report = document.getElementById("adminPrintReport");

        if (!report) {
            console.error("[admin-charts] Print report area not found.");
            return;
        }

        const chartIds = [
            "reportChart",
            "registeredUsersChart",
            "searchKeywordsChart",
        ];

        const chartImages = [];

        // Convert each ECharts chart to an image
        chartIds.forEach((id) => {
            const dom = document.getElementById(id);

            if (!dom) return;

            const chart = echarts.getInstanceByDom(dom);

            if (!chart) {
                console.warn(`[admin-charts] Chart not found: ${id}`);
                return;
            }

            chart.resize();

            const image = chart.getDataURL({
                type: "png",
                pixelRatio: 2,
                backgroundColor: "#ffffff",
            });

            chartImages.push({
                id,
                image,
            });
        });

        // Save original chart contents
        const originalContents = {};

        chartImages.forEach(({ id, image }) => {
            const element = document.getElementById(id);

            if (!element) return;

            originalContents[id] = element.innerHTML;

            element.innerHTML = "";

            const img = document.createElement("img");

            img.src = image;
            img.alt = "";

            img.style.width = "100%";
            img.style.height = "auto";
            img.style.display = "block";

            element.appendChild(img);
        });

        // Activate print mode
        document.body.classList.add("printing-report");

        // Give browser time to render the generated images
        setTimeout(() => {
            window.print();
        }, 500);

        // Restore dashboard after Print / Cancel
        window.onafterprint = function () {
            chartImages.forEach(({ id }) => {
                const element = document.getElementById(id);

                if (!element) return;

                element.innerHTML = originalContents[id];

                const chart = echarts.getInstanceByDom(element);

                if (chart) {
                    chart.resize();
                }
            });

            document.body.classList.remove("printing-report");
        };
    };
}
