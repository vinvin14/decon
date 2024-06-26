<script setup>
import Head from "@/Components/Head.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {computed, onBeforeMount, onMounted, ref} from "vue";
import {useFormatter} from "@/Composables/formatter.js";
import {Line} from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js'
import {forEach} from "lodash";
import {faPhoneFlip, faPhoneVolume, faTag} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import DropdownFilter from "@/Components/Filter/DropdownFilter.vue";
import {router} from "@inertiajs/vue3";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const { formatCurrency, formatNumber } = useFormatter();

const purchaseOrderReports = ref({
    pending_delivery_count: 0,
    pending_delivery_amount: 0.00,
    delivered_count: 0,
    delivered_amount: 0.00,
    cancelled_count: 0,
    cancelled_amount: 0.00,
});

const productReports = ref({});

const customerReports = ref({});

const chartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Revenue',
            backgroundColor: '#13dc4d',
            borderColor: '#13dc4d',
            borderWidth: 2,
            data: [],
        }
    ],
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        tooltip: {
            callbacks: {
                label: function (context) {
                    if (context.parsed.y !== null) {
                        return formatCurrency(context.parsed.y);
                    }
                },
            },
        }
    },
};

const monthOptions = [
    { value: '', label: 'All Months' },
    { value: '1', label: 'January' },
    { value: '2', label: 'February' },
    { value: '3', label: 'March' },
    { value: '4', label: 'April' },
    { value: '5', label: 'May' },
    { value: '6', label: 'June' },
    { value: '7', label: 'July' },
    { value: '8', label: 'August' },
    { value: '9', label: 'September' },
    { value: '0', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

const yearOptions = [];

for (let i = 2024; i <= 2050; i++) {
    yearOptions.push({
        value: i.toString(),
        label: i.toString(),
    });
}

function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

onBeforeMount(() => {
    if (!route().params['year']) {
        router.get(route('admin.dashboard', {
            year: new Date().toLocaleString('en-PH', {
                timeZone: 'Asia/Manila',
                year: 'numeric'
            })
        }));
    }
});

onMounted(() => {
    let tempData = [];

    if (route().params['month']) {
        for (let i = 1; i <= getDaysInMonth(route().params['year'], route().params[['month']]); i++) {
            chartData.value.labels.push(i);
            tempData.push(0);
        }
    } else {
        chartData.value.labels = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        tempData = [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00];
    }

    window.axios.get(route('admin.reports.purchase_orders', {
            month: route().params['month'],
            year: route().params['year'],
        }))
        .then(response => {
            forEach(response.data, x => {
                tempData[(route().params['month'] ? x.day : x.month) - 1] = parseFloat(x.delivered_amount);

                purchaseOrderReports.value.pending_delivery_count += parseInt(x.pending_delivery_count);
                purchaseOrderReports.value.pending_delivery_amount += parseInt(x.pending_delivery_amount);
                purchaseOrderReports.value.delivered_count += parseInt(x.delivered_count);
                purchaseOrderReports.value.delivered_amount += parseInt(x.delivered_amount);
                purchaseOrderReports.value.cancelled_count += parseInt(x.cancelled_count);
                purchaseOrderReports.value.cancelled_amount += parseInt(x.cancelled_amount);
            });

            chartData.value.datasets[0].data = tempData;
        });

    window.axios.get(route('admin.reports.products', {
            month: route().params['month'],
            year: route().params['year'],
        }))
        .then(response => productReports.value = response.data);

    window.axios.get(route('admin.reports.customers', {
            month: route().params['month'],
            year: route().params['year'],
        }))
        .then(response => customerReports.value = response.data);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard">
            <template #actions>
                <div class="flex items-center space-x-3 px-6 md:px-0">
                    <DropdownFilter :options="monthOptions" field="month" :includePage="false" />
                    <DropdownFilter :options="yearOptions" field="year" :includePage="false" />
                </div>
            </template>
        </Head>

        <div class="mt-6 md:mt-12 flex flex-col space-y-6 md:space-y-12 px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-1 md:gap-y-0 md:gap-x-6">
                <div class="flex flex-col items-end rounded p-3 bg-cyan-500">
                    <h3 class="text-white">{{ formatNumber(purchaseOrderReports.pending_delivery_count) }} Pending Delivery Orders</h3>
                    <p class="text-lg text-white font-medium">{{ formatCurrency(purchaseOrderReports.pending_delivery_amount) }}</p>
                </div>
                <div class="flex flex-col items-end rounded p-3 bg-green-500">
                    <h3 class="text-white">{{ formatNumber(purchaseOrderReports.delivered_count) }} Delivered Orders</h3>
                    <p class="text-lg text-white font-medium">{{ formatCurrency(purchaseOrderReports.delivered_amount) }}</p>
                </div>
                <div class="flex flex-col items-end rounded p-3 bg-red-500">
                    <h3 class="text-white">{{ formatNumber(purchaseOrderReports.cancelled_count) }} Cancelled Orders</h3>
                    <p class="text-lg text-white font-medium">{{ formatCurrency(purchaseOrderReports.cancelled_amount) }}</p>
                </div>
            </div>

            <div class="w-full h-72">
                <Line v-if="chartData.datasets[0].data.length" :data="chartData" :options="chartOptions" />
            </div>

            <div class="flex flex-col space-y-6">
                <div class="col-span-2 flex flex-col space-y-3">
                    <h3 class="text-lg font-medium">Best Sellers</h3>
                    <div class="grid grid-cols-2 md:grid-cols-8 gap-6">
                        <div href="#" v-for="product in productReports">
                            <div class="w-full p-1 rounded-t">
                                <img :src="`/storage/images/${product.image ?? 'placeholder.png'}`" class="mx-auto rounded" />
                            </div>
                            <div class="flex flex-col items-center space-y-1 px-4 py-2">
                                <p class="w-full text-center text-xs truncate" :title="product.name">{{ product.name }}</p>
                                <p class="text-xs">{{ product.brand ?? 'No Brand' }}</p>
                                <p>{{ formatNumber(product.qty) }} sold</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 flex flex-col space-y-3">
                    <h3 class="text-lg font-medium">Top Customers</h3>
                    <table class="w-full">
                        <tr v-for="customer in customerReports" class="border-b hover:bg-neutral-50 dark:hover:bg-neutral-900">
                            <td class="p-3">
                                <div class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-1 md:space-y-0">
                                    <p class="font-medium">{{ customer.email }}</p>
                                    <div class="flex flex-col md:flex-row md:items-center md:space-x-3">
                                        <p class="text-sm flex items-center space-x-1">
                                            <FontAwesomeIcon :icon="faTag" class="text-center" />
                                            <span>{{ customer.code }}</span>
                                        </p>
                                        <p class="text-sm flex items-center space-x-1">
                                            <FontAwesomeIcon :icon="faPhoneFlip" class="text-center" />
                                            <span class="italic">{{ customer.contact_number }}</span>
                                        </p>
                                        <p class="text-sm flex items-center space-x-1">
                                            <FontAwesomeIcon :icon="faPhoneVolume" class="text-center" />
                                            <span class="italic">{{ customer.viber_id ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                    <p>{{ formatCurrency(customer.amount) }}</p>
                                </div>
                            </td>
                            <td class="p-3 hidden md:block">
                                <p class="text-right">{{ formatCurrency(customer.amount) }}</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
