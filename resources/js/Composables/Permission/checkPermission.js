import { usePage } from "@inertiajs/vue3";
import _ from 'lodash'

const checkPermissionComposable = (permission_code) => {
    let status = _.filter(usePage().props.auth.user.permissions, {'code' : permission_code})
    if(status.length >= 1) return true;
    return false;
}

export default checkPermissionComposable;
