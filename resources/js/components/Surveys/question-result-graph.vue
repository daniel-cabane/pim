<template>
    <div>
        <div class="d-flex">
            <Pie :data="data" :options="pieOptions" v-if="isPie"/>
            <Bar :data="data" :options="barOptions" v-else/>
        </div>
        <div class="d-flex justify-end">
            <v-btn variant="tonal" color="primary" class="mt-5" @click="initColors">
                Change colors
            </v-btn>
        </div>
    </div>
</template>
<script setup>
    import { ref } from "vue";
    import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
    import { Bar, Pie } from 'vue-chartjs'
    import { computed } from "vue";

    ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

    const props = defineProps({ question: Object, language: String , isPie: Boolean});

    const colors = ref([]);
    const initColors = () => {
        let hue = Math.random();
        const colorSet = [];
        for (let i = 0; i < props.question.answers.length; i++) {
            hue += 0.618033988749895;
            hue %= 1;

            const {r, g, b} = hsvToRgb(hue, 0.5, 0.95);

            colorSet.push(`rgba(${r}, ${g}, ${b}, 0.4)`);
        }
        colors.value = colorSet;
    }
    const hsvToRgb = (h, s, v) => {
        let h_i = Math.floor(h * 6);
        let f = h * 6 - h_i;
        let p = v * (1 - s);
        let q = v * (1 - f * s);
        let t = v * (1 - (1 - f) * s);

        let mod = h_i % 6;
        let r =  Math.round([v, q, p, p, t, v][mod]*255);
        let g =  Math.round([t, v, v, q, p, p][mod]*255);
        let b =  Math.round([p, p, t, v, v, q][mod]*255);

        return {r, g, b};
    }
    initColors();

    const data = computed(() => ({
        labels: props.question.options.map(a => a[props.language]),
        datasets: [{ 
            label: false,
            data: props.question.answers.map(q => q.length),
            backgroundColor: colors.value
        }],
    }));

    const getOrCreateTooltip = (chart) => {
        let tooltipEl = chart.canvas.parentNode.querySelector('div');

        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.style.background = 'rgba(0, 0, 0, 0.7)';
            tooltipEl.style.borderRadius = '3px';
            tooltipEl.style.color = 'white';
            tooltipEl.style.opacity = 1;
            tooltipEl.style.pointerEvents = 'none';
            tooltipEl.style.position = 'absolute';
            tooltipEl.style.transform = 'translate(-50%, 0)';
            tooltipEl.style.transition = 'all .1s ease';

            const table = document.createElement('table');
            table.style.margin = '0px';

            tooltipEl.appendChild(table);
            chart.canvas.parentNode.appendChild(tooltipEl);
        }

        return tooltipEl;
        }

    const externalTooltipHandler = (context) => {
        const {chart, tooltip} = context;
        const tooltipEl = getOrCreateTooltip(chart);

        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }

        if (tooltip.body) {
            const titleLines = tooltip.title || [];
            const bodyLines = tooltip.body.map(b => b.lines);

            const tableHead = document.createElement('thead');

            titleLines.forEach(title => {
            const tr = document.createElement('tr');
            tr.style.borderWidth = 0;

            const th = document.createElement('th');
            th.style.borderWidth = 0;
            const text = document.createTextNode(title);

            th.appendChild(text);
            tr.appendChild(th);
            tableHead.appendChild(tr);
            });

            const tableBody = document.createElement('tbody');
            props.question.answers[context.tooltip.dataPoints[0].dataIndex].forEach(name => {
                const tr = document.createElement('tr');
                tr.style.backgroundColor = 'inherit';
                tr.style.borderWidth = 0;

                const td = document.createElement('td');
                td.style.borderWidth = 0;

                const text = document.createTextNode(name);

                td.appendChild(text);
                tr.appendChild(td);
                tableBody.appendChild(tr);
            });

            const tableRoot = tooltipEl.querySelector('table');
            while (tableRoot.firstChild) {
            tableRoot.firstChild.remove();
            }
            tableRoot.appendChild(tableHead);
            tableRoot.appendChild(tableBody);
        }

        const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;

        tooltipEl.style.opacity = 1;
        tooltipEl.style.left = positionX + tooltip.caretX + 'px';
        tooltipEl.style.top = positionY + tooltip.caretY + 'px';
        tooltipEl.style.font = tooltip.options.bodyFont.string;
        tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
    }

    const barOptions = { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: { enabled: false, external: externalTooltipHandler }
        },
        scales: {y: {ticks: { stepSize: 1 }}}
    }
    const pieOptions = { 
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            tooltip: { enabled: false, external: externalTooltipHandler }
        },
    }
</script>