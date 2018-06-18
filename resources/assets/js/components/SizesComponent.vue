<template>
    <table-component :data="response">
        <table-column show="provider" label="Provider"></table-column>
        <table-column show="region" label="Region"></table-column>
        <table-column show="plan" label="Plan/Group/Type"></table-column>
        <table-column show="name" label="Name"></table-column>
        <table-column show="cpu" label="CPU (cores)" data-type="numeric"></table-column>
        <table-column show="ram" label="RAM (MiB)" data-type="numeric"></table-column>
        <table-column show="disk" label="Disk (GiB)" data-type="numeric"></table-column>
        <table-column show="transfer" label="Transfer Limit" data-type="numeric"></table-column>
        <table-column show="dollars_per_hr" label="USD/hour" data-type="numeric" :formatter="hourly"></table-column>
        <table-column show="dollars_per_mo" label="USD/month" data-type="numeric" :formatter="monthly"></table-column>
    </table-component>
</template>

<script>
    var called = false;

    export default {
        watch: {
            watchMe: {
                handler: function (newVal, oldVal) {
                    this.fetchData();
                },
                immediate: true,
            },
        },
        methods: {
            async fetchData() {
                if (called == false) {
                    called = true;

                    var providers = (await axios.get('/api/providers')).data;
                    for (var provider in providers) {
                        this.response = this.response.concat((await axios.get('/api/sizes/' + provider)).data);
                    }
                }
            },
            hourly(value, rowProperties) {
                var formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 4,
                });

                return formatter.format(value);
            },
            monthly(value, rowProperties) {
                var formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                });

                return formatter.format(value);
            },
        },
        data: function () {
            return {
                response: [],
                watchMe: true,
            };
        },
    }
</script>
