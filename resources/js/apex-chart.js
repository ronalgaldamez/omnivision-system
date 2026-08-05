/**
 * Función global para renderizar gráficos ApexCharts dentro de componentes Livewire.
 * Se define globalmente para que esté disponible en cualquier navegación (SPA o recarga completa).
 */
window.apexChart = function (id, type, height, series, categories, labels, colors) {
    return {
        chart: null,
        init() {
            if (typeof ApexCharts === 'undefined') return;
            const el = document.getElementById(id);
            if (!el) return;

            if (el._apexChart) {
                el._apexChart.destroy();
            }

            const isDonut = type === 'donut';
            const isRadial = type === 'radial';

            const options = {
                chart: {
                    type: isDonut ? 'donut' : type,
                    height: height,
                    toolbar: { show: false },
                    fontFamily: 'DM Sans, sans-serif',
                    animations: { enabled: true },
                },
                series: series,
                colors: colors,
                labels: labels,
                dataLabels: { enabled: false },
                stroke: isDonut ? { width: 3 } : { curve: 'smooth', width: 2 },
                legend: {
                    position: 'bottom',
                    labels: { colors: '#6b7280', fontSize: '11px' },
                    itemMargin: { horizontal: 8 },
                },
                tooltip: { theme: 'light' },
                grid: { borderColor: '#f3f4f6' },
            };

            if (isDonut) {
                options.plotOptions = {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#9ca3af',
                                fontSize: '12px',
                                fontWeight: 500,
                                formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                            },
                            value: {
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#111827',
                            },
                        },
                    },
                };
            } else if (isRadial) {
                options.plotOptions = {
                    radialBar: {
                        hollow: { size: '70%' },
                        dataLabels: {
                            name: { show: true, fontSize: '13px', color: '#6b7280' },
                            value: {
                                fontSize: '30px',
                                fontWeight: 700,
                                color: '#111827',
                                formatter: (val) => val + '%',
                            },
                        },
                        track: { background: '#f1f5f9' },
                    },
                };
                options.fill = { type: 'solid' };
            } else {
                options.fill = {
                    type: type === 'area' ? 'gradient' : 'solid',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.75,
                        opacityTo: 0.1,
                        stops: [0, 90, 100],
                    },
                };
                if (type === 'bar') {
                    options.plotOptions = {
                        bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' },
                    };
                }
                options.xaxis = {
                    categories: categories,
                    labels: { style: { colors: '#9ca3af', fontSize: '11px' } },
                };
            }

            this.chart = new ApexCharts(el, options);
            this.chart.render();
            el._apexChart = this.chart;
        },
    };
};
