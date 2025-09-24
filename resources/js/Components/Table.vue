<script setup>
defineProps({
    items: {
        type: Array,
        default: [],
    },
    columns: {
        type: Array,
        default: [],
    }
});
</script>

<template>
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in items" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
            </tr>
        </tbody>
    </table>
</template>
