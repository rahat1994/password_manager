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
                <div class="sidebar_search_input_wrapper">
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

            <el-col :span="10">
                <el-table
                    :data="displayVaultItems"
                    style="width: 100%">
                    <el-table-column
                    label="All"
                    type="selection"
                    width="55">
                    </el-table-column>
                    <el-table-column
                    label="Name"
                    width="180">
                    <template slot-scope="scope">
                        <span style="margin-left: 10px">{{ scope.row.name }}</span>
                        <br>
                        <span style="margin-left: 15px">{{ scope.row.username }}</span>
                    </template>
                    </el-table-column>
                    <el-table-column
                    label="Owner"
                    width="180">
                    <template slot-scope="scope">
                        <div slot="reference" class="name-wrapper">
                            <el-tag size="medium">{{ scope.row.organisation.name }}</el-tag>
                        </div>
                    </template>
                    </el-table-column>
                    <el-table-column
                        width="80"
                        prop="select"
                        label="options">
                    <!-- <template slot-scope="scope">
                        <i class="el-icon-more" @click="handleDelete(scope.$index, scope.row)"></i>
                    </template> -->
                        <template slot-scope="scope">
                            <el-dropdown trigger="click">
                                <span class="el-dropdown-link">
                                    <i class="el-icon-more" @click="handleDelete(scope.$index, scope.row)"></i>
                                </span>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item @click="handleDelete(scope.$index, scope.row)" icon="el-icon-plus">Action 1</el-dropdown-item>
                                    <el-dropdown-item icon="el-icon-circle-plus">Action 2</el-dropdown-item>
                                    <el-dropdown-item icon="el-icon-circle-plus-outline">Action 3</el-dropdown-item>
                                    <el-dropdown-item icon="el-icon-check">Action 4</el-dropdown-item>
                                    <el-dropdown-item icon="el-icon-circle-check">Action 5</el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </template>
                    
                    </el-table-column>
                </el-table>
                <el-pagination
                    background
                    @size-change="changePerPage"
                    @current-change="changeCurrentPage"
                    @currentPage="currentPage"
                    :page-size="perPage"
                    :page-sizes="[10, 20, 30, 40, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="total"
                >
                </el-pagination>
            </el-col>
        </el-row>
    </div>
</template>
<style>

    .sidebar_search_input_wrapper{
        padding: 10px;
        background-color: #545c64;
        margin-bottom: 10px;
    }
  .el-dropdown-link {
    cursor: pointer;
    color: #409EFF;
  }
  .el-icon-arrow-down {
    font-size: 12px;
  }
  .el-dropdown-menu{
    padding: 10px 0;
    margin: 5px 0;
    background-color: #fff;
    border: 1px solid #ebeef5;
    border-radius: 4px;
    box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
  }
  .demonstration {
    display: block;
    color: #8492a6;
    font-size: 14px;
    margin-bottom: 20px;
  }
  .el-dropdown-menu__item:not(.is-disabled):hover {
    background-color: #ecf5ff;
    color: #66b1ff;
}
.el-dropdown-menu__item {
    list-style: none;
    line-height: 36px;
    padding: 0 20px;
    margin: 0;
    font-size: 14px;
    color: #606266;
    cursor: pointer;
    outline: none;
}
</style>
<script type="text/babel">
    export default {
        name: 'Vault',
        data() {
            return {
                message: "Hello there",
                searchTerm:"",
                page: 1,
                currentPage: 1,
                perPage: 20,
                total: 0,
                tableData: [{
                            date: '2016-05-03',
                            name: 'Tom',
                            address: 'No. 189, Grove St, Los Angeles'
                            }, {
                            date: '2016-05-02',
                            name: 'Tom',
                            address: 'No. 189, Grove St, Los Angeles'
                            }, {
                            date: '2016-05-04',
                            name: 'Tom',
                            address: 'No. 189, Grove St, Los Angeles'
                            }, {
                            date: '2016-05-01',
                            name: 'Tom',
                            address: 'No. 189, Grove St, Los Angeles'
                        }],
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

                vaultItems: [],
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
            handleEdit(index, row) {
                console.log(index, row);
            },
            handleDelete(index, row) {
                console.log(index, row);
            },
            setActive() {
                this.active = this.$route.meta.parent || this.$route.name;
            },
            changeCurrentPage(val) {
                this.currentPage = val
            },

            changePerPage(val) {
                this.perPage = val
            },
        },
        computed: {
            displayVaultItems() {

                // if (this.search == null) return this.data
                // this.filtered = this.data.filter(
                //     (data) =>
                //     !this.search ||
                //     this.searchBy.some((item) => data[item].toString().toLowerCase().includes(this.search.toLowerCase()))
                // )

                this.total = this.vaultItems.length
                return this.vaultItems.slice(this.perPage * this.currentPage - this.perPage, this.perPage * this.currentPage)
            }
        },
        created() {
            for (let i = 1; i <= 60; i++) {
                this.vaultItems.push(            {
                        id: i,
                        name: `Hoyt Holland ${i}`,
                        username: "myusername",
                        password: "mypassword",
                        organisation:{
                            name: "Staff Asia",
                            id: 1
                        }

                    });
            }

        }
    };
</script>
