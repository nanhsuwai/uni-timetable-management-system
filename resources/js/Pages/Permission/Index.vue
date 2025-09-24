<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, computed, reactive } from "vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiShape } from "@mdi/js";
import _ from 'lodash'
import BaseButton from "@/Components/BaseButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import checkPermissionComposable from "@/Composables/Permission/checkPermission.js";
import { faSlack } from "@fortawesome/free-brands-svg-icons";

const showPermissionUpdate = ref(false);
const selectedPermission = ref({});

let hasPermission = ref(checkPermissionComposable("manage_users_management"));

const props = defineProps({
  permission_types: {
    type: Object,
    default: {},
  },
  granted_systems: {
    type: Object,
    default: {}
  }
});

const form = useForm({
  type_id: "",
  checkedPermission: []
});

const showPermissionModal = (permission) => {
    let data = [];
    showPermissionUpdate.value = true;
    selectedPermission.value = permission;

    permission.permissions.map(permission => {
      return data.push(permission.id);
    });

    form.type_id = selectedPermission.value.id;
    form.checkedPermission = data;
};


const updatePermission = () => {
    form.post(route('permissions:create'), {
        preserveScroll: true,
        onSuccess: () => {
          closePermissionModal()
            toast.add({
                message: "Permission Update !"
            })
        },
        onError: () => form.reset(),
        onFinish: () => form.reset(),
    });
};

const closePermissionModal = () => {
  showPermissionUpdate.value = false;
};

const allChecked = computed({
  get() {
    form.checkedPermission.length == props.granted_systems.length
  },
  set(value) {
     form.checkedPermission = value ? props.granted_systems.map(granted_system => granted_system.id) : [];
  }
})
</script>

<template>
  <Head title="Permissions" />

  <LayoutAuthenticated>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        User Type Permissions
      </h2>
    </template>

    <SectionMain
      v-if="
        $page.props.auth.user.user_type == 'admin' ||
        $page.props.auth.user.user_type == 'course_manager' ||
        hasPermission
      "
    >
      <div class="py-6">
        <div class="min-w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
          <SectionTitleLineWithButton
            :icon="mdiShape"
            title="User Type Permissions"
          >
            <!-- <BaseButton
                            label="Add"
                            color="contrast"
                            small
                            rounded-full
                            @click.prevent="showAddPermissionModal"
                        /> -->
          </SectionTitleLineWithButton>
          <div class="flex justify-between items-center">
            <div></div>
            <div>
              <h6 class="font-extrabold capitalize text-sm mb-4 mr-2">
                Total - {{ props.permission_types.meta.total }}
              </h6>
            </div>
          </div>

          <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <CardBox has-table>
              <table>
                <thead>
                  <tr class="font-extrabold">
                    <td class="text-center">Sr. No</td>
                    <td class="text-center">Name</td>
                    <td class="text-center">Code</td>
                    <td class="text-center">Actions</td>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(permission, index) in props.permission_types.data"
                    class="text-xs"
                    :key="index"
                  >
                    <td class="text-center w-40" data-label="ID">
                      {{
                        props.permission_types.meta.per_page *
                          (props.permission_types.meta.current_page - 1) +
                        index +
                        1
                      }}
                    </td>
                    <td class="text-center w-80" data-label="Granted System">
                      {{ permission.name }}
                    </td>
                    <td class="text-center w-80" data-label="Permission Name">
                      {{ permission.code }}
                    </td>
                    <td class="text-center">
                      <div>
                        <button
                          @click="showPermissionModal(permission)"
                          class="border-2 outline-none hover:bg-yellow-600 border-yellow-600 rounded mx-2 px-2 hover:text-white text-xs"
                        >
                          Edit
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <PaginationLinks :links="props.permission_types.meta.links" />
            </CardBox>
          </div>
        </div>

        <Modal :show="showPermissionUpdate" @close="closePermissionModal">
          <div class="p-6">
            <h2 class="w-full text-lg font-medium text-gray-900 text-center">
              Create Permission
            </h2>
            <hr />

            <div class="mt-6 flex items-center">
              <h3 class="pr-4">
                Username:
                <span class="font-extrabold">{{ selectedPermission.name }}</span>
              </h3>
              <!-- <input type="checkbox" v-model="allChecked" /> &nbsp; Check All -->
            </div>
            <div class="">
              <ul>
                <li
                  v-for="granted_system in props.granted_systems"
                  :key="granted_system.id"
                  class="grid grid-cols-2 gap-2"
                >
                  Granted System : {{ granted_system.name }} <br />
                  <ul
                    v-for="granted_permission in granted_system.permissions"
                    :key="granted_permission.id"
                  >
                    <li>
                      <input
                        v-model="form.checkedPermission"
                        :value="granted_permission.id"
                        type="checkbox"
                        :name="granted_permission.name"
                      />
                      &nbsp;
                      <span>{{ granted_permission.name }}</span>
                    </li>
                  </ul>
                  <div class="my-4"></div>
                </li>
              </ul>
            </div>

            <div class="mt-6 flex justify-end">
              <SecondaryButton @click="closePermissionModal">
                Cancel
              </SecondaryButton>

              <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="updatePermission"
              >
                Update
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div>
    </SectionMain>
    <SectionMain v-else>
      <h2>No Permission !</h2>
    </SectionMain>
  </LayoutAuthenticated>
</template>
