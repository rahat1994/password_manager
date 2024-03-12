<template>
    <div class="content" style="background-color: #f5f7fa;">
        <el-row class="tac" :gutter="20">
            <el-col :span="4">
                <el-menu
                default-active="2"
                class="el-menu-vertical-demo menu"
                background-color="#545c64"
                text-color="#fff" 
                active-text-color="#ffd04b"
                @open="handleOpen"
                @close="handleClose">
                <div class="sidebar_search_inpot_wrapper">
                    <el-input
                        placeholder="Search"
                        v-model="searchTerm"
                        prefix-icon="el-icon-search"
                        clearable>
                    </el-input>
                </div>

                <el-submenu index="1">
                    <template slot="title">
                    <i class="el-icon-location"></i>
                    <span>{{ this.$t('All Vaults') }}</span>
                    </template>
                    <el-menu-item-group>
                        <el-menu-item v-bind:key="vault.id" v-for="vault in vaults" :index="1-vault.id">{{ vault.name }}</el-menu-item>
                    </el-menu-item-group>
                    <el-menu-item-group title="Group Two">
                    <el-menu-item index="1-3">item three</el-menu-item>
                    </el-menu-item-group>
                    <el-submenu index="1-4">
                    <template slot="title">item four</template>
                    <el-menu-item index="1-4-1">item one</el-menu-item>
                    </el-submenu>
                </el-submenu>
                <el-menu-item index="2">
                    <i class="el-icon-menu"></i>
                    <span>{{ this.$t('All Vaults') }}</span>
                </el-menu-item>
                </el-menu>
            </el-col>
            <el-col :span="16" style="background-color: blueviolet;">
                {{ message }}
            </el-col>
        </el-row>
    </div>
</template>
<style>
.sidebar_search_inpot_wrapper{
    padding: 10px;
    background-color: #545c64;
    margin-bottom: 10px;
}
</style>

<script type="text/babel">
    export default {
        name: 'Vault',
        data() {
            return {
                message: "Hello there",
                searchTerm:"",
                vaults: [
                    {
                        name: "Vault 1",
                        id: 1
                    },
                    {
                        name: "Vault 2",
                        id: 2
                    },
                    {
                        name: "Vault 3",
                        id: 3
                    }
                ],
                items: [],
                folders: [],
                collections: [],
            }
        },
        watch: {
            '$route'(to, from) {
                if (this.$route.name) {
                    this.setActive();
                }
            }
        },
        methods: {
            handleOpen(key, keyPath) {
                this.message = "Hello there";
                console.log(key, keyPath);
            },
            handleClose(key, keyPath) {
                console.log(key, keyPath);
            },
            defaultRoutes() {
                return [
                    {
                        route: 'connections',
                        title: this.$t('Settings')
                    },
                    {
                        route: 'test',
                        title: this.$t('Email Test')
                    },
                    {
                        route: 'logs',
                        title: this.$t('Email Logs')
                    },
                    {
                        route: 'notification_settings',
                        title: this.$t('Alerts')
                    },
                    {
                        route: 'support',
                        title: this.$t('About')
                    },
                    {
                        route: 'docs',
                        title: this.$t('Documentation')
                    }
                ];
            },
            setMenus() {
                this.items = this.applyFilters('fluentmail_top_menus', this.defaultRoutes());
                this.setActive();
            },
            setActive() {
                this.active = this.$route.meta.parent || this.$route.name;
            }
        },
        computed: {
            brandLogo() {
                const src = this.appVars.brand_logo;
                return `<img style="width:140px;" src="${src}" />`;
            }
        },
        created() {
            jQuery('.update-nag,.notice:not(.fluentsmtp_urgent), #wpbody-content > .updated, #wpbody-content > .error').remove();
            this.logo = `<div class='logo'>${this.brandLogo}</div>`;
            this.setMenus();
        }
    };
</script>
