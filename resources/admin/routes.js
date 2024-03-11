import Dashboard from './Modules/Dashboard/Dashboard';
import Connections from './Modules/Settings/Connections';
import Connection from './Modules/Settings/Connection';
import Logs from './Modules/Logger/Logs';
import Test from './Modules/Test/Test';
import Support from './Modules/Misc/Support';
import Docs from './Modules/Misc/Docs';
import NotificationSettings from './Modules/NotificationSettings/NotificationSettings.vue';
import Vault from './Modules/Vault/Vault.vue';
export default [
    {
        name: 'Vault',
        path: '/',
        meta: {
            title: 'Vault',
        },
        component: Vault,
    },
    {
        name: 'Tools',
        path: '/tools',
        meta: {
            title: 'Tools',
        },
        component: Dashboard,
    }
];
