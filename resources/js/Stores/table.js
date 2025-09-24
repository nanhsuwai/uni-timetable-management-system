import { reactive } from "vue";

export default reactive({
    items: {
        headingRow: {
            style: "bg-blue-300 border border-gray-300 text-left px-1 py-2 text-xs text-center"
        }
    }

    // add(toast) {
    //     this.items.unshift({
    //         key: Symbol(),
    //         ...toast
    //     })
    // },
    // remove(index) {
    //     this.items.splice(index, 1)
    // }
})