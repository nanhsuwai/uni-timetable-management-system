import "../css/main.css";

import { createPinia } from "pinia";
import { useStyleStore } from "@/Stores/style.js";
import { darkModeKey, styleKey } from "@/config.js";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* import specific icons */
import { faCheckCircle, faPlus, faTrash, faUserSecret } from '@fortawesome/free-solid-svg-icons'
import { faCircleCheck } from '@fortawesome/free-regular-svg-icons';
import axios from "axios";


const appName =
  window.document.getElementsByTagName("title")[0]?.innerText || "Timetable Information System";

const pinia = createPinia();

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob("./Pages/**/*.vue")
    ),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .component('font-awesome-icon', FontAwesomeIcon)
      .use(plugin)
      .use(pinia)
      .use(ZiggyVue, Ziggy)
      .mount(el);
  },
  progress: {
    color: "#4B5563",
  },
});

const styleStore = useStyleStore(pinia);

/* App style */
styleStore.setStyle(localStorage[styleKey] ?? "basic");

/* Dark mode */
if (
  (!localStorage[darkModeKey] &&
    window.matchMedia("(prefers-color-scheme: dark)").matches) ||
  localStorage[darkModeKey] === "1"
) {
  styleStore.setDarkMode(true);
}
