<template>
    <div class="content" style="
    background-color: #f5f7fa;
    ">
        <el-row class="tac">
            <el-col :span="4">
                <el-menu
                default-active="2"
                class="el-menu-vertical-demo menu"
                background-color="#545c64"
                text-color="#fff" 
                active-text-color="#ffd04b"
                @open="handleOpen"
                @close="handleClose">
                <el-input
                    placeholder="Search"
                    v-model="searchTerm"
                    prefix-icon="el-icon-search"
                    clearable>
                </el-input>
                <el-submenu index="1">
                    <template slot="title">
                    <i class="el-icon-location"></i>
                    <span>Navigator One</span>
                    </template>
                    <el-menu-item-group title="Group One">
                    <el-menu-item index="1-1">item one</el-menu-item>
                    <el-menu-item index="1-2">item one</el-menu-item>
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
                    <span>Navigator Two</span>
                </el-menu-item>
                <el-menu-item index="3" disabled>
                    <i class="el-icon-document"></i>
                    <span>Navigator Three</span>
                </el-menu-item>
                <el-menu-item index="4">
                    <i class="el-icon-setting"></i>
                    <span>Navigator Four</span>
                </el-menu-item>
                </el-menu>
            </el-col>
            <el-col :span="16">
                {{ message }}
            </el-col>
        </el-row>
    </div>

</template>
<script type="text/babel">
    export default {
        name: 'Vault',
        data() {
            return {
                searchTerm:"",
                vaults: [],
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
