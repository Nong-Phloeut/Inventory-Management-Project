import { createApp } from "vue"
import { createPinia } from 'pinia'
import App from "./App.vue"
import router from "./router"
import vuetify from "./plugins/vuetify"
import i18n from "./plugins/i18n"
import "./style.css"

import CustomTitle from "./components/global/CustomTitle.vue";
import BaseButton from "./components/customs/BaseButton.vue";
import Notif from '@/components/global/Notification.vue'
import Confirm from '@/components/global/Confirm.vue'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(vuetify)
app.use(router)
app.use(i18n)
app.component("CustomTitle", CustomTitle);
app.component("BaseButton", BaseButton);
app.config.globalProperties.$notif = Notif.value?.newAlert
app.config.globalProperties.$confirm = Confirm.value?.open

app.mount("#app")