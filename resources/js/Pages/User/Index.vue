<script setup>
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUpdated, watch } from "vue";
import PaginationLinks from "@/Components/PaginationLinks.vue";
import Modal from "@/Components/Modal.vue";
import toast from "@/Stores/toast"
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LayoutAuthenticated from "@/Layouts/LayoutAuthenticated.vue";
import _ from "lodash";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { mdiShapePlus } from "@mdi/js";
import debounce from "lodash/debounce";
import checkPermissionComposable from "@/Composables/Permission/checkPermission.js";
import VueMultiselect from 'vue-multiselect';

const props = defineProps({
  users: {
    type: Object,
    default: {},
  },
  types: {
    type: Object,
    default: {},
  },

  filters: {
    type: Object,
    default: {},
  },



  userTypes: {
    type: Object,
    default: {}
  },
  permissions: {
    type: Object,
    default: {}
  }
});

let hasPermission = ref(checkPermissionComposable("manage_users_management"));
let filterUsername = ref(props.filters.filterUsername);
let filterType = ref(props.filters.filterType);

watch([filterUsername, filterType], debounce(function ([
  filterUsernameValue,
  filterTypeValue,
]) {
  router.get(route('users:all'), {
    filterUsername: filterUsernameValue,
    filterType: filterTypeValue
  })
}, 300));

const form = useForm({
  username: "",
  email: "",
  phone: "",
  office_phone: "",
  office_address: "",
  name_prefix: "",
  date_of_birth: "",
  gender: "",
  employee_type: "",
  myanmar_name: "",
  english_name: "",
  job_description: "",
  education: "",
  employee_number: "",
  employee_card_photo: "",
  profile_photo: "",
  user_type: "",
  password: "",
  nrc_number: "",
  ministry_id: "",
  department_id: "",
  position_id: "",
});

const permissionForm = useForm({
  user_id: '',
  checkedPermission: []
})

const formType = useForm({
  user_id: "",
  user_type: usePage().props.auth.user.user_type,
});

const updatePasswordForm = useForm({
  password: '',
  password_confirmation: ''
});

const confirmingUserCreation = ref(false);
const confirmShowType = ref(false);
const showPermissionModal = ref(false);
const showUpdatePasswordModal = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref({});
const selectedPermissionUser = ref({});
const deletedUser = ref({});

const showPermissionUpdateForm = (user) => {
    let data = [];

    showPermissionModal.value = true;
    selectedPermissionUser.value = user

    user.permissions.map(permission => {
      return data.push(permission.id);
    });

    permissionForm.user_id = selectedPermissionUser.value.id;
    permissionForm.checkedPermission = data;
}

const showUpdatePasswordForm = (user) => {
  showUpdatePasswordModal.value = true;
  selectedUser.value = user;
}

const deleteUserModal = (user) => {
  showDeleteModal.value = true;
  deletedUser.value = user;
}

const updatePermission = () => {
  permissionForm.post(route('users:create:permission', {
    user: selectedPermissionUser.value
  }), {
      preserveScroll: true,
      onSuccess: () => {
        closePermissionUpdateModal();
          toast.add({
              message: "Permission Update !"
          })
      },
      onError: () => permissionForm.reset(),
      onFinish: () => permissionForm.reset(),
  });
}

const updatePassword = (selectedUser) => {
  updatePasswordForm.put(route('users:update:password', selectedUser),{
    preserveScroll: true,
    onSuccess: () => {
      closeUpdatePasswordModal();
      toast.add({
        message: 'Password updated!'
      });
    },
    onError: () => {
      closeUpdatePasswordModal();
    }
  })
}


const createUser = () => {
  form.nrc_number =
    selectedRegion.value +
    selectedTownship.value +
    selectedCitizen.value +
    selectedNumber.value;
  form.ministry_id = selectedMinistry.value;
  form.department_id = selectedDepartment.value;
  form.post(route("users:user:create"), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.add({
        message: "User Added !",
      });
    },
    onError: () => form.reset(),
    onFinish: () => form.reset(),
  });
};

const editType = () => {
  formType.post(
    route("users:update", {
      user: formType.user_id,
    }),
    {
      preserveScroll: true,
      onSuccess: () => {
        closeTypeModal();
        toast.add({
          message: "Successful update !",
        });
      },
    }
  );
};

const deleteUser = () => {
   router.delete(route('users:delete', deletedUser.value), {
      preserveScroll: true,
      onSuccess: () => {
          closeDeleteModal();
          toast.add({
            message: 'Successfully delete!'
          });
      }
   })
}

const showAddUserModal = () => {
  confirmingUserCreation.value = true;
};

const showTypeModal = (user) => {
  formType.user_id = user.id;
  formType.user_type = user.user_type;
  confirmShowType.value = true;
};

const closeModal = () => {
  confirmingUserCreation.value = false;
  form.reset();
};

const closeTypeModal = () => {
  confirmShowType.value = false;

  formType.reset();
};

const closeUpdatePasswordModal = () => {
  showUpdatePasswordModal.value = false;
  updatePasswordForm.reset();
}

const closePermissionUpdateModal = () => {
  showPermissionModal.value = false;
  permissionForm.reset();
}

const closeDeleteModal = () => {
  showDeleteModal.value = false;
}

onMounted(() => {
  props.users.data.forEach((user) => {
    const str = user.user_type;
    const words = str.split("_");
    const capitalizedWords = words.map(
      (word) => word.charAt(0).toUpperCase() + word.slice(1)
    );
    const result = capitalizedWords.join(" ");
    user.user_type_new = result;
    return result;
  });
});

onUpdated(() => {
  props.users.data.forEach((user) => {
    const str = user.user_type;
    const words = str.split("_");
    const capitalizedWords = words.map(
      (word) => word.charAt(0).toUpperCase() + word.slice(1)
    );
    const result = capitalizedWords.join(" ");
    user.user_type_new = result;
    return result;
  });
});

const selectedRegion = ref("");
const selectedTownship = ref("");
const selectedCitizen = ref("");
const selectedNumber = ref("");

const filterTownships = computed(() => {
  if (selectedRegion.value) {
    return props.nrc_townships.filter(
      (township) => township.nrc_region_id === parseInt(selectedRegion.value)
    );
  }
  return [];
});

watch(selectedRegion, (newValue) => {
  if (!newValue) {
    selectedTownship.value = "";
  }
});

const selectedMinistry = ref("");
const selectedDepartment = ref("");

const filterDepartments = computed(() => {
  if (selectedMinistry.value) {
    return props.departments.filter(
      (department) =>
        department.ministry_id === parseInt(selectedMinistry.value)
    );
  }

  return [];
});

watch(selectedMinistry, (newValue) => {
  if (!newValue) {
    selectedDepartment.value = "";
  }
});

const allChecked = computed({
  get() {
    const allPermissionIds = Object.values(props.permissions).flat().map(p => p.id);
    return permissionForm.checkedPermission.length === allPermissionIds.length;
  },
  set(value) {
    const allPermissionIds = Object.values(props.permissions).flat().map(p => p.id);
    permissionForm.checkedPermission = value ? allPermissionIds : [];
  }
})

const clear = () => {
  router.get(route('users:all'));
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Users" />
    <SectionMain
      v-if="
        $page.props.auth.user.user_type == 'admin' ||
        $page.props.auth.user.user_type == 'course_manager' ||
        hasPermission
      "
    >
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <SectionTitleLineWithButton
            :icon="mdiShapePlus"
            title="Users Management"
          >
            <BaseButton
              label="Add"
              color="contrast"
              small
              rounded-full
              @click.prevent="showAddUserModal"
            />
          </SectionTitleLineWithButton>
          <div
            class="grid gap-2 py-6 border border-gray-500 px-3 mb-4 text-xs rounded sm:grid-cols-1 md:grid-cols-2"
          >
            <div class="my-1">
              <label for="username" class="block mb-3 text-xs">Username</label>
              <TextInput
                v-model="filterUsername"
                id="username"
                class="w-full rounded border-gray-300 text-xs h-10 text-gray-700"
                type="text"
                autocomplete="off"
                placeholder="Enter username"
              />
            </div>
            <div class="my-1">
              <label for="course" class="block mb-3 text-xs">User Type</label>
              <VueMultiselect
                  v-model="filterType"
                  :options="props.userTypes"
                  label="name">
              </VueMultiselect>
            </div>
            <div class='flex justify-start items-center'>
              <button @click="clear" class="py-2 px-4 rounded-lg hover:bg-slate-700 hover:text-slate-100 bg-slate-600 text-slate-50 focus:outline-none">
                    Clear
              </button>
            </div>
          </div>
          <div class="flex justify-end align-middle mr-2 mb-4">
            <h6 class="font-extrabold capitalize text-xs mr-2">
              Total - {{ props.users.meta.total }}
            </h6>
          </div>
          <CardBox has-table>
            <table class="text-xs">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>User Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(user, index) in props.users.data" :key="index">
                  <td>
                    {{
                      props.users.meta.per_page *
                        (props.users.meta.current_page - 1) +
                      index +
                      1
                    }}
                  </td>
                  <td data-label="Name">{{ user.name }}</td>
                  <td data-label="Email">{{ user.email }}</td>
                  <td data-label="User Type" class="capitalize">
                    {{ user.user_type_new }}
                  </td>
                  <td>
                    <button
                      @click.prevent="showTypeModal(user)"
                      class="border-2 outline-none hover:bg-green-600 border-green-600 rounded p-1 mr-1 hover:text-white"
                    >
                      Edit Type
                    </button>
                    <button
                      @click="showPermissionUpdateForm(user)"
                      class="border-2 outline-none hover:bg-indigo-600 border-indigo-600 rounded ml-1 p-1 hover:text-white text-xs"
                    >
                      Edit Permission
                    </button>
                    <button
                      @click.prevent="showUpdatePasswordForm(user)"
                      class="border-2 outline-none hover:bg-yellow-600 border-yellow-600 rounded p-1 ml-2 hover:text-white"
                    >
                      Edit Password
                    </button>
                    <button
                      @click.prevent="deleteUserModal(user)"
                      class="border-2 outline-none hover:bg-red-600 border-red-600 rounded p-1 ml-2 hover:text-white"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardBox>
          <div
            class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800"
          >
            <pagination-links :links="props.users.meta.links" />
          </div>

          <Modal :show="showPermissionModal" @close="closePermissionUpdateModal">
            <div class="p-6 max-h-96 overflow-y-auto">
              <h2 class="w-full text-lg font-medium text-gray-900 text-center">
                Update Permission
              </h2>
              <hr />

              <div class="mt-6 flex items-center justify-between">
                <h3 class="pr-4">
                  Username:
                  <span class="font-extrabold">{{ selectedPermissionUser.name }}</span>
                </h3>
                <label class="flex items-center">
                  <input type="checkbox" v-model="allChecked" class="mr-2" />
                  Check All
                </label>
              </div>
              <div class="mt-4">
                <div v-for="(permissions, module) in props.permissions" :key="module" class="mb-4">
                  <h4 class="font-semibold text-md mb-2">{{ module }}</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                      <input
                        v-model="permissionForm.checkedPermission"
                        :value="permission.id"
                        type="checkbox"
                        :name="permission.name"
                        class="mr-2"
                      />
                      <span class="text-sm">{{ permission.name }}</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closePermissionUpdateModal">
                  Cancel
                </SecondaryButton>

                <PrimaryButton
                  class="ml-3"
                  :class="{ 'opacity-25': permissionForm.processing }"
                  :disabled="permissionForm.processing"
                  @click="updatePermission"
                >
                  Update
                </PrimaryButton>
              </div>
            </div>
          </Modal>
          <Modal :show="showUpdatePasswordModal" @close="closeUpdatePasswordModal">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900">Update User Password</h2>
              <div class="py-5">
                <InputLabel for="email" value="Email" />
                <TextInput
                  id="email"
                  type="text"
                  disabled="true"
                  autofocus="off"
                  :value="selectedUser.email"
                  class="block w-full mt-1"
                  autocomplete="off"
                />
              </div>
              <div class="py-5">
                <InputLabel for="password" value="Enter your password" />
                <TextInput
                  id="password"
                  v-model="updatePasswordForm.password"
                  type="password"
                  autofocus="off"
                  class="block w-full mt-1"
                  placeholder="Enter a new password"
                  autocomplete="off"
                />
              </div>
              <div>
                <InputLabel for="password_confirmation" value="Confirm your password" />
                <TextInput
                  id="password_confirmation"
                  v-model="updatePasswordForm.password_confirmation"
                  type="password"
                  class="block w-full mt-1"
                  placeholder="Confirm your password"
                  autocomplete="off"
                />
              </div>

              <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeUpdatePasswordModal">
                  Cancel
                </SecondaryButton>

                <PrimaryButton
                  class="ml-3"
                  :class="{ 'opacity-25': updatePasswordForm.processing }"
                  :disabled="updatePasswordForm.processing"
                  @click="updatePassword(selectedUser)"
                >
                  Update
                </PrimaryButton>
              </div>
             </div>
          </Modal>
          <Modal :show="confirmShowType" @close="closeTypeModal">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900">Edit User Type</h2>
              <div>
                <select
                  v-model="formType.user_type"
                  class="mt-1 block w-full border-gray-300 rounded"
                >
                  <option
                    v-for="type_list in props.types"
                    :value="type_list.code"
                    :key="type_list.id"
                    class="capitalize my-3 py-4"
                    :selected="
                      $page.props.auth.user.user_type == type_list.code
                    "
                  >
                    {{ type_list.name }}
                  </option>
                </select>
              </div>
              <div class="mt-6 flex justify-end">
                <SecondaryButton @click.prevent="closeTypeModal">
                  Cancel
                </SecondaryButton>

                <PrimaryButton
                  class="ml-3"
                  :class="{ 'opacity-25': formType.processing }"
                  :disabled="formType.processing"
                  @click="editType"
                >
                  Update
                </PrimaryButton>
              </div>
            </div>
          </Modal>
          <Modal :show="confirmingUserCreation" @close="closeModal">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900">Add User</h2>
              <div class="grid grid-cols-2 gap-2">
                <div class="mt-6">
                  <InputLabel for="myanmar_name" value="အမည် (မြန်မာ)" />

                  <div class="flex">
                    <select
                      v-model="form.name_prefix"
                      name="name_prefix"
                      id="name_prefix"
                      class="mt-1 block w-1/4 border-gray-300 rounded mx-1"
                    >
                      <option value="ဦး">ဦး</option>
                      <option value="ဒေါ်">ဒေါ်</option>
                      <option value="ဒေါက်တာ">ဒေါက်တာ</option>
                    </select>
                    <TextInput
                      id="myanmar_name"
                      ref="myanmar_name"
                      v-model="form.myanmar_name"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="Myanmar Name"
                    />
                  </div>

                  <InputError
                    :message="form.errors.myanmar_name"
                    class="mt-2"
                  />
                </div>
                <div class="mt-6">
                  <InputLabel for="english_name" value="အမည် (English)" />
                  <TextInput
                    id="name"
                    v-model="form.english_name"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="English Name"
                  />
                  <InputError
                    :message="form.errors.english_name"
                    class="mt-2"
                  />
                </div>
              </div>
              <div class="mt-6">
                <div class="mb-2">
                  <InputLabel
                    for="nrc_number"
                    value="နိုင်ငံသားစိစစ်ရေးကတ်ပြားအမှတ်"
                  />
                </div>
                <div class="grid grid-cols-4 gap-1">
                  <select
                    v-model="selectedRegion"
                    name="nrc_region"
                    id="nrc_region"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">ပြန်နယ်/တိုင်း</option>
                    <option
                      v-for="nrc_region in props.nrc_regions"
                      :value="nrc_region.region_en"
                    >
                      {{ nrc_region.region_en }}
                    </option>
                  </select>
                  <select
                    v-model="selectedTownship"
                    name="nrc_township"
                    id="nrc_township"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">မြို့နယ်</option>
                    <option
                      v-for="nrc_township in filterTownships"
                      :value="nrc_township.township_en"
                    >
                      {{ nrc_township.township_en }}
                    </option>
                  </select>
                  <select
                    v-model="selectedCitizen"
                    name="nrc_citizen"
                    id="nrc_citizen"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">(နိင်)</option>
                    <option
                      v-for="nrc_citizen in props.nrc_citizens"
                      :value="nrc_citizen.citizen_en"
                    >
                      {{ nrc_citizen.citizen_en }}
                    </option>
                  </select>
                  <input
                    v-model="selectedNumber"
                    type="text"
                    maxlength="6"
                    class="mt-1 block w-full border-gray-300 rounded"
                    placeholder="123456"
                  />
                </div>
                <InputError :message="form.errors.nrc_number" class="mt-2" />
              </div>
              <div class="grid grid-cols-2 gap-2 items-center mt-6">
                <div>
                  <InputLabel for="username" value="Username" />
                  <TextInput
                    id="name"
                    v-model="form.username"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Username"
                  />
                  <InputError :message="form.errors.username" class="mt-2" />
                </div>
                <div class="">
                  <InputLabel for="date_of_birth" value="မွေးနေ့" />
                  <TextInput
                    id="date_of_birth"
                    v-model="form.date_of_birth"
                    type="date"
                    class="mt-1 block w-full"
                    placeholder="Date Of Birth"
                  />
                  <InputError
                    :message="form.errors.date_of_birth"
                    class="mt-2"
                  />
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div class="mt-6">
                  <InputLabel for="education" value="ပညာအရည်အချင်း" />

                  <TextInput
                    id="education"
                    v-model="form.education"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="B.E (IT)"
                  />

                  <InputError :message="form.errors.education" class="mt-2" />
                </div>
                <div class="mt-6">
                  <InputLabel for="gender" value="ကျား/မ" class="mb-2" />
                  <div class="inline-flex">
                    <input
                      type="radio"
                      v-model="form.gender"
                      value="female"
                      class="mt-1 block px-2 mx-2"
                    />
                    Female
                    <input
                      type="radio"
                      v-model="form.gender"
                      value="male"
                      class="mt-1 block px-2 mx-2"
                    />
                    Male
                  </div>
                  <InputError :message="form.errors.gender" class="mt-2" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div class="mt-6">
                  <InputLabel for="employee_number" value="ဝန်ထမ်းအမှတ်" />
                  <TextInput
                    id="employee_number"
                    v-model="form.employee_number"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded"
                    placeholder="Employee Number"
                  />
                  <InputError
                    :message="form.errors.employee_number"
                    class="mt-2"
                  />
                </div>
                <div class="mt-6">
                  <InputLabel for="employee_type" value="ဝန်ထမ်းအမှတ်" />
                  <select
                    v-model="form.employee_type"
                    name="employee_type"
                    id="employee_type"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">ဝန်ထမ်းအမျိုးအစားရွေးချယ်ပါ...</option>
                    <option value="1">အမှုထမ်း</option>
                    <option value="2">အရာထမ်း</option>
                  </select>
                  <InputError
                    :message="form.errors.employee_type"
                    class="mt-2"
                  />
                </div>
              </div>

              <div class="grid grid-cols-3 gap-2">
                <div class="mt-6">
                  <InputLabel for="email" value="အီးမေးလ်" />
                  <TextInput
                    id="email"
                    v-model="form.email"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="abc@gmail.com"
                  />
                  <InputError :message="form.errors.email" class="mt-2" />
                </div>
                <div class="mt-6">
                  <InputLabel for="phone" value="လျှောက်ထားသူ၏ ဖုန်းနံပါတ်" />
                  <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="09-xxx-xxx-xxxx"
                  />
                  <InputError :message="form.errors.phone" class="mt-2" />
                </div>
                <div class="mt-6">
                  <InputLabel
                    for="office_phone"
                    value="ရုံးဖုန်းနံပါတ်/ဖက်စ်"
                  />
                  <TextInput
                    id="office_phone"
                    v-model="form.office_phone"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="09-xxx-xxx-xxxx"
                  />
                  <InputError
                    :message="form.errors.office_phone"
                    class="mt-2"
                  />
                </div>
              </div>

              <div class="grid grid-cols-3 gap-2">
                <div class="mt-6">
                  <InputLabel
                    for="ministry"
                    value="ဝန်ကြီးဌာန/အဖွဲ့အစည်း"
                    class="mb-2"
                  />
                  <select
                    v-model="selectedMinistry"
                    name="ministry"
                    id="ministry"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">
                      ဝန်ကြီးဌာန/အဖွဲ့အစည်း ရွေးချယ်ပါ...
                    </option>
                    <option
                      v-for="ministry in props.ministries"
                      :value="ministry.id"
                    >
                      {{ ministry.name }}
                    </option>
                  </select>

                  <InputError
                    :message="form.errors.profile_photo"
                    class="mt-2"
                  />
                </div>
                <div class="mt-6">
                  <InputLabel
                    for="department"
                    value="ဦးစီးဌာန/လုပ်ငန်း"
                    class="mb-2"
                  />
                  <select
                    v-model="selectedDepartment"
                    name="department"
                    id="department"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">ဦးစီးဌာန/လုပ်ငန်း ရွေးချယ်ပါ...</option>
                    <option
                      v-for="department in filterDepartments"
                      :value="department.id"
                    >
                      {{ department.name }}
                    </option>
                  </select>
                </div>
                <div class="mt-6">
                  <InputLabel for="position" value="ရာထူး" class="mb-2" />
                  <select
                    v-model="form.position_id"
                    name="position"
                    id="position"
                    class="mt-1 block w-full border-gray-300 rounded"
                  >
                    <option value="">ရာထူး ရွေးချယ်ပါ...</option>
                    <option
                      v-for="position in props.positions"
                      :value="position.id"
                    >
                      {{ position.name }}
                    </option>
                  </select>
                </div>
              </div>

              <div class="mt-6">
                <InputLabel
                  for="job_description"
                  value="လုပ်ငန်းတာဝန်"
                  class="mb-2"
                />
                <textarea
                  v-model="form.job_description"
                  name="job_description"
                  id="job_description"
                  rows="4"
                  placeholder="လုပ်ငန်းတာဝန်"
                  class="w-full rounded border-gray-300"
                ></textarea>
              </div>
              <div class="mt-6">
                <InputLabel
                  for="office_address"
                  value="ရုံးလိပ်စာ"
                  class="mb-2"
                />
                <textarea
                  v-model="form.office_address"
                  name="office_address"
                  id="office_address"
                  rows="4"
                  placeholder="ရုံးလိပ်စာ"
                  class="w-full rounded border-gray-300"
                ></textarea>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div class="mt-6">
                  <InputLabel
                    for="profile_photo"
                    value="ဓာတ်ပုံတင်ရန်"
                    class="mb-2"
                  />
                  <input
                    type="file"
                    @input="form.profile_photo = $event.target.files[0]"
                    class="border-gray-300 rounded-lg py-1"
                  />

                  <InputError
                    :message="form.errors.profile_photo"
                    class="mt-2"
                  />
                </div>
                <div class="mt-6">
                  <InputLabel
                    for="employee_card_photo"
                    value="ဝန်ထမ်းကဒ်"
                    class="mb-2"
                  />
                  <input
                    type="file"
                    @input="form.employee_card_photo = $event.target.files[0]"
                    class="border-gray-300 rounded-lg py-1"
                  />

                  <InputError
                    :message="form.errors.employee_card_photo"
                    class="mt-2"
                  />
                </div>
              </div>
              <div class="mt-6">
                <InputLabel
                  for="employee_card_photo"
                  value="User Type"
                  class="mb-2"
                />
                <select
                  v-model="form.user_type"
                  class="mt-1 block w-full border-1 border-gray-300 rounded"
                >
                  <option
                    v-for="type_list in props.types"
                    :value="type_list.code"
                    :key="type_list.id"
                  >
                    {{ type_list.name }}
                  </option>
                </select>
                <InputError :message="form.errors.user_type" class="mt-2" />
              </div>
              <div class="mt-6 flex justify-end">
                <SecondaryButton @click.prevent="closeModal">
                  Cancel
                </SecondaryButton>
                <PrimaryButton
                  class="ml-3"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                  @click.prevent="createUser"
                >
                  Add
                </PrimaryButton>
              </div>
            </div>
          </Modal>
          <Modal :show="showDeleteModal" @close="closeDeleteModal">
              <div class="p-5">
                <h2 class="w-full text-lg font-medium text-red-500">
                  Delete User
                </h2>
                <hr>
                <p class="w-full text-md font-medium text-gray-900">Are you sure you want delete?</p>
              </div>
              <div class="py-4 px-4 mb-4 float-right">
                <SecondaryButton @click.prevent="closeDeleteModal">
                  Cancel
                </SecondaryButton>
                <PrimaryButton
                  class="ml-3"
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                  @click.prevent="deleteUser"
                >
                  Confirm
                </PrimaryButton>
              </div>
          </Modal>
        </div>
      </div>
    </SectionMain>
    <SectionMain v-else>
      <h2>No Permissions</h2>
    </SectionMain>
  </LayoutAuthenticated>
</template>
