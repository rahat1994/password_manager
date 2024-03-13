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

            <el-col :span="10" style="background-color:red">

                <div class="content_header_wrapper">
                    <el-row>
                        <el-col >
                            <h2>{{ contentHeaderTitle }}</h2>
                        </el-col>

                    </el-row>
                </div>


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

                <div class="pagination_element_wrapper">
                    <el-pagination
                        background
                        @current-change="changeCurrentPage"
                        @currentPage="currentPage"
                        :page-size="perPage"
                        layout="total, prev, pager, next, jumper"
                        :total="total"
                    >
                    </el-pagination>
                </div>

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
.pagination_element_wrapper{
    padding: 10px;
    background-color: #fff;
    margin-top: 10px;
}

.content_header_wrapper{
    padding: 10px;
    background-color: #fff;
    margin-bottom: 10px;
}
</style>
<script type="text/babel">
    export default {
        name: 'Vault',
        data() {
            return {
                contentHeaderTitle: "All Vault",
                searchTerm:"",
                page: 1,
                currentPage: 1,
                perPage: 8,
                total: 0,
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
                filtered: [],
                searchBy: ["name", "username"],
                vaultItems: [],
                folders: [],
                collections: [],
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

                if (this.searchTerm == ""){
                    return this.vaultItems.slice(this.perPage * this.currentPage - this.perPage, this.perPage * this.currentPage)
                } 
                this.filtered = this.vaultItems.filter(
                    (data) =>
                    !this.searchTerm ||
                    this.searchBy.some((item) => data[item].toString().toLowerCase().includes(this.searchTerm.toLowerCase()))
                )

                this.total = this.filtered.length
                return this.filtered.slice(this.perPage * this.currentPage - this.perPage, this.perPage * this.currentPage)
            }
        },
        created() {
            for (let i = 1; i <= 60; i++) {

                if(i == 10){
                    this.vaultItems.push({
                        id: i,
                        name: `Rahat Holland ${i}`,
                        username: "myusername",
                        password: "mypassword",
                        organisation:{
                            name: "Staff Asia",
                            id: 1
                        }

                    });
                }
                else if(i == 20){
                    this.vaultItems.push({
                        id: i,
                        name: `Hoyt Holland ${i}`,
                        username: "rahatname",
                        password: "mypassword",
                        organisation:{
                            name: "Staff Asia",
                            id: 1
                        }

                    });
                } else{
                    this.vaultItems.push({
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

        }
    };
</script>
