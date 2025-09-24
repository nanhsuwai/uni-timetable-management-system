<script setup>
    import SectionMain from '@/Components/SectionMain.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
    defineProps({ permissions: Array })

</script>

<template>
    <AuthenticatedLayout>
        <Head title="Permissions" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users Permission</h2>
        </template>
        <SectionMain v-if="$page.props.auth.user.user_type == 'admin'">
            <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="w-full table table-auto text-left">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="permission in permissions" :key="permission.id">
                                    <td>{{ permission.id }}</td>
                                    <td>{{ permission.name }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>
        </SectionMain>
        <SectionMain v-else>
            <h3>No Permissions !</h3>
        </SectionMain>
        
    </AuthenticatedLayout>
</template>