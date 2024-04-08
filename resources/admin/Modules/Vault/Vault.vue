<template>
    <div v-if="!loadingItems" class="content" style="background-color: #f5f7fa;">
        <el-row class="tac" :gutter="20">
            <el-col :span="5" style="height:100%">
                <el-menu class="el-menu-vertical-demo menu" background-color="#545c64" text-color="#fff"
                    :default-active="activeMenuItem" active-text-color="#ffd04b" @open="handleOpen"
                    @close="handleClose">
                    <el-menu-item-group v-if="folders.length" :title="$t('Folders')" >
                        <el-menu-item  v-bind:key="folder.id" v-for="folder in folders"
                            :index="'2-'+folder.id.toString()" @click="folderSelected(folder)">
                            {{ folder.name }}
                        </el-menu-item>
                    </el-menu-item-group>
                    <el-menu-item-group v-else :title="$t('No Folders')"></el-menu-item-group>
                </el-menu>  
            </el-col>

            <el-col :span="18">

                <div class="fss_header">
                    <VaultBulkActions :selected="selectedVaultItems" @on-bulk-action="handleVaultBulkAction"
                        v-if="selectedVaultItems.length" />

                    <div v-if="!selectedVaultItems.length" style="float:left;margin-top:6px;">{{ contentHeaderTitle }}
                    </div>

                    <div style="float:right;margin-left: 6px;">
                        <el-button @click="renderNewPage" type="success" size="small">
                            <i class="el-icon-refresh"></i></el-button>
                    </div>

                    <div style="float:right;">
                        <el-input clearable size="small" v-model="filter.searchTerm" @clear="filter.searchTerm=''"
                            @keyup.enter.native="fetchItems" :placeholder="$t('Type & press enter...')">
                            <el-button slot="append" icon="el-icon-search" @click="fetchItems" />
                        </el-input>
                    </div>

                    <VaultHeaderButton :folders="this.folders" @on-refresh-items="refreshPage"
                        @on-refresh-folders="fetchFolders" />

                </div>

                <el-table stripe :data="vaultItems" style="width: 100%" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="50">
                    </el-table-column>
                    <el-table-column :label="$t('Name')">
                        <template slot-scope="scope">
                            <span @click="() => editItem(scope.row)"
                                style="margin-left: 10px; font-weight:bold; cursor:pointer;">
                                <a>{{ scope.row.name }}</a>
                            </span>
                            <br>
                            <span style="margin-left: 15px">{{ scope.row.username }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('Owner')">
                        <template slot-scope="scope">
                            <div slot="reference" class="name-wrapper">
                                <el-tag size="medium">{{ scope.row.organisation.name }}</el-tag>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="select" :label="$t('Options')">
                        <!-- <template slot-scope="scope">
                        <i class="el-icon-more" @click="handleDelete(scope.$index, scope.row)"></i>
                    </template> -->
                        <template slot-scope="scope">
                            <el-dropdown trigger="click" @command="handleItemDropDownCommand">
                                <span class="el-dropdown-link">
                                    <i class="el-icon-more"></i>
                                </span>
                                <template>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item command="copy_username" class="copy_username" :data-username="scope.row.username" @click.native="copyUserName(scope.$index, scope.row)"
                                            icon="el-icon-document-copy dropdown_item">{{ $t("Copy Username")
                                            }}</el-dropdown-item>
                                        <el-dropdown-item command="copy_password" @click.native="copyPassword(scope.$index, scope.row)"
                                            icon="el-icon-document-copy dropdown_item">{{ $t("Copy Password")
                                            }}</el-dropdown-item>
                                        <el-dropdown-item divided command="delete_item" @click.native="deleteItems(scope.row.id)"
                                            icon="el-icon-delete dropdown_item danger">
                                            <span class="danger"> {{ $t("Delete") }}</span>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </template>

                    </el-table-column>
                </el-table>

                <div class="pagination_element_wrapper">
                    <el-pagination background @current-change="changeCurrentPage" :current-page="pagination.currentPage"
                        :page-size="pagination.perPage" layout="total, prev, pager, next"
                        :total="pagination.total">
                    </el-pagination>
                </div>

            </el-col>
        </el-row>

        <VaultItemCreationDialog :isItemCreationDialogVisible="isItemEditingDialogVisible" :folders="folders"
            :form="itemEditingDialogData" :context="'edit_item'" :item_id="Number(itemEditingDialogData.id)"
            @on-item-creation-dialog-closed="handleItemCreationDialogClosed" />

        <VaultBulkFolderUpdateDialog :isVisible="isBulkFolderUpdateDialogVisible" :folders="folders"
            :selectedItems="selectedVaultItems"
            @on-folder-update-dialog-closed="handleBulkUpdateDialogClosed" />

        <VaultConfirmMasterPassword :isVisible="isPasswordConfirmationDialogVisible" :itemId="itemEditingDialogData.id" 
         @on-master-pass-confirmation-dialog-closed="handleMasterPasswordConfirmed" :context="this.passwordConfirmationContext"/>
    </div>
    <el-skeleton :animated="true" v-else class="fss_content" :rows="15"></el-skeleton>
</template>
<script type="text/babel">
    import { Loading } from "element-ui";
    import VaultBulkActions from "./VaultBulkActions.vue";
    import VaultHeaderButton from "./VaultHeaderButton.vue";
    import VaultItemCreationDialog from "./VaultItemCreationDialog.vue";
    import VaultBulkFolderUpdateDialog from "./VaultBulkFolderUpdateDialog.vue";
    import VaultConfirmMasterPassword from "./VaultConfirmMasterPassword.vue";
    import ClipboardJS from 'clipboard';

    export default {
        name: 'Vault',
        components: {
            VaultBulkActions,
            VaultHeaderButton,
            VaultItemCreationDialog,
            VaultBulkFolderUpdateDialog,
            VaultConfirmMasterPassword
        },
        data() {
            return {
                contentHeaderTitle: "All Vault",
                filter: {
                    searchTerm: '',
                    folderId:null,
                    collectionId:null,
                },
                isItemEditingDialogVisible: false,
                isBulkFolderUpdateDialogVisible: false,
                isPasswordConfirmationDialogVisible: false,
                itemEditingDialogData: {},
                passwordConfirmationContext: null,
                page: 1,
                loading:false,
                loadingFolders:false,
                loadingItems:false,
                pagination: {
                    total: 0,
                    perPage: 10,
                    currentPage: 1
                },
                selectedFolder: null,
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
                selectedVaultItems: [],
                folders: [],
                collections: [],
                currentItem: {},
                itemData:{}
            }
        },
        methods: {
            handleItemDropDownCommand(command){
                if(command === 'copy_username'){
                    // this.copyUserName();
                } else if(command === 'copy_password'){
                    // this.copyPassword();
                } else if(command === 'delete_item'){
                    // this.deleteItems(this.currentItem.id);
                }
            },
            copyUserName(index, row) {
                // Create a temporary button element
                const tempButton = document.createElement('button');
                tempButton.style.display = 'none'; // Hide the button
                document.body.appendChild(tempButton); // Append the button to the body

                // Create a new ClipboardJS instance
                const clipboard = new ClipboardJS(tempButton, {
                    text: () => row.username // Replace 'username' with the actual property name if it's different
                });

                // Trigger a click on the temporary button
                tempButton.click();

                // Remove the temporary button and destroy the ClipboardJS instance
                document.body.removeChild(tempButton);
                clipboard.destroy();

                this.$notify.success({
                    title: 'Great!',
                    offset: 19,
                    message: "Username copied to clipboard!"
                });
            },
            copyPassword(index, row) {

                if (row.masterPassProtected) {
                    this.isPasswordConfirmationDialogVisible = true;
                    this.passwordConfirmationContext = 'copy_password';
                    this.currentItem = row;
                    return;
                }
                // Create a temporary button element
                const tempButton = document.createElement('button');
                tempButton.style.display = 'none'; // Hide the button
                document.body.appendChild(tempButton); // Append the button to the body

                // Create a new ClipboardJS instance
                const clipboard = new ClipboardJS(tempButton, {
                    text: () => row.password // Replace 'password' with the actual property name if it's different
                });

                // Trigger a click on the temporary button
                tempButton.click();

                // Remove the temporary button and destroy the ClipboardJS instance
                document.body.removeChild(tempButton);
                clipboard.destroy();
            },
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
                this.currentItem = row;
                console.log(index, row);
            },
            setActive() {
                this.active = this.$route.meta.parent || this.$route.name;
            },
            changeCurrentPage(val) {
                this.pagination.currentPage = val
                this.renderNewPage();
            },
            changePerPage(val) {
                this.pagination.perPage = val
            },
            handleSelectionChange(val) {
                this.selectedVaultItems = val;
            },
            handleVaultBulkAction({action}) {
                console.log(action);
                if (action === 'moveselected') {
                    this.isBulkFolderUpdateDialogVisible = true;
                } else if (action === 'deleteselected') {
                    console.log('Delete Selected');
                    const ifDelete = confirm(('Are you sure you want to delete selected items?'));
                    console.log(ifDelete);
                    if(ifDelete){
                        this.deleteItems();
                    } else{
                        return;
                    }
                }
            },
            deleteItems(itemId){

                var data = {};
                console.log("Item deletion process");
                console.log(itemId);
                if(itemId === null || itemId === undefined){
                    data = {
                        itemId: this.selectedVaultItems.map(item => item.id)
                    };
                } else {
                    data = {
                        itemId: [itemId]
                    };
                }

                this.loadingFolders = true;
                this.$post('item/delete', data).then(res => {
                    console.log(res.data);
                }).fail(error => {
                    console.log(error);
                }).always(() => {
                    this.fetchItems();
                });
            },
            fetchFolders(){
                const data = {};
                this.loadingFolders = true;
                this.$get('folder', data).then(res => {
                    console.log(res.data);
                    this.folders = res.data;
                }).fail(error => {
                    console.log(error);
                }).always(() => {
                    this.loadingFolders = false;
                });
            },
            fetchItems(){

                this.loadingItems = true;

                this.itemData = {
                    per_page: this.pagination.perPage,
                    page: this.pagination.currentPage,
                    search: this.filter.searchTerm,
                    folderId: this.filter.folderId
                };

                this.$router.replace({ query: this.itemData });

                this.$get('item', this.itemData).then(res => {

                    this.vaultItems = [];
                    this.pagination.total = res.total;
                    const page = Number(this.$route.query.page);
                    this.pagination.currentPage = page || this.pagination.currentPage;
                    this.vaultItems = this.formatItems(res.data);                    
                }).fail(error => {
                    console.log(error);
                }).always(() => {
                    this.loadingItems = false;
                });
            },
            formatItems(items){
                jQuery.each(items, (i, item) => {
                    items[i] = this.formatItem(item);
                });
                return items;
            },
            formatItem(item){
                return {
                    id: item.id,
                    name: item.name,
                    username: item.username,
                    url: item.login_url,
                    password: item.password,
                    folder: item.folder_id,
                    organisation: {
                        name: "Default",
                        id: 1
                    },
                    itemType: 'login',
                    desc: item.note,
                    masterPassProtected: (item.master_pass_secured === "1") ? true : false
                };
            },
            refreshPage({refreshPage}){
                console.log(refreshPage);
                this.fetchItems();
            },
            renderNewPage(){
                this.fetchFolders();
                this.fetchItems();
            },
            folderSelected(folder){
                this.pagination.currentPage = 1;
                if (this.filter.folderId === folder.id){
                    this.filter.folderId = null;
                    this.selectedFolder = null;                    
                } else {
                    this.filter.folderId = folder.id
                    this.selectedFolder = folder;
                }
                this.renderNewPage();
            },
            handleItemCreationDialogClosed(closeItemCreationDialog){
                this.isItemEditingDialogVisible = false;
                this.renderNewPage();
            },
            handleBulkUpdateDialogClosed(closeBulkUpdateDialog){
                console.log(closeBulkUpdateDialog);
                this.isBulkFolderUpdateDialogVisible = false;
                if (closeBulkUpdateDialog.fetchItems) {
                    this.fetchItems();
                }
            },
            editItem(item){
                this.itemEditingDialogData = item
                
                if (item.masterPassProtected) {
                    this.isPasswordConfirmationDialogVisible = true;
                    // this.itemEditingDialogData = item;
                } else {
                    this.isItemEditingDialogVisible = true;
                    this.itemEditingDialogData = item;
                }
            },
            handlePasswordConfirmationDialogClosed(){
                this.isPasswordConfirmationDialogVisible = false;
            },
            handleMasterPasswordConfirmed(data){
                const { success, item, context } = data;
                // this.isPasswordConfirmationDialogVisible = false;
                if(success === null){
                    this.isPasswordConfirmationDialogVisible = false;
                    return;
                }
                if (success && context !== 'copy_password') {

                    //  format item first and then assign it to itemEditingDialogData
                    this.itemEditingDialogData = this.formatItem(item);
                    this.isItemEditingDialogVisible = true;
                } else{
                    var currentItem = this.formatItem(item);
                    // Create a temporary button element
                    const tempButton = document.createElement('button');
                    tempButton.style.display = 'none'; // Hide the button
                    document.body.appendChild(tempButton); // Append the button to the body

                    // Create a new ClipboardJS instance
                    const clipboard = new ClipboardJS(tempButton, {
                        text: () => currentItem.password // Replace 'password' with the actual property name if it's different
                    });

                    // Trigger a click on the temporary button
                    tempButton.click();

                    // Remove the temporary button and destroy the ClipboardJS instance
                    document.body.removeChild(tempButton);
                    clipboard.destroy();

                    this.$notify.success({
                        title: 'Great!',
                        offset: 19,
                        message: "Password copied to clipboard!"
                    });
                }
                this.handlePasswordConfirmationDialogClosed();
            }
        },
        computed: {
            displayVaultItems() {

                if (this.filter.searchTerm == ""){
                    this.pagination.total = this.vaultItems.length
                    return this.vaultItems.slice(this.pagination.perPage * this.pagination.currentPage - this.pagination.perPage, this.pagination.perPage * this.pagination.currentPage)
                } 
                this.filtered = this.vaultItems.filter(
                    (data) =>
                    !this.filter.searchTerm ||
                    this.searchBy.some((item) => data[item].toString().toLowerCase().includes(this.filter.searchTerm.toLowerCase()))
                )

                this.pagination.total = this.filtered.length
                return this.filtered.slice(this.pagination.perPage * this.pagination.currentPage - this.pagination.perPage, this.pagination.perPage * this.pagination.currentPage)
            },
            activeMenuItem() {
                if(this.filter.folderId){
                    return '2-' + this.filter.folderId;

                }
                return null;
            }
        },
        created() {
            
            const currentPage = this.$route.query.page;

            if (currentPage) {
                this.pagination.currentPage = Number(currentPage);
            }

            if (this.$route.query.status) {
                this.filter.status = this.$route.query.status;
            }

            if (this.$route.query.search) {
                this.filter.searchTerm = this.$route.query.search;
            }

            if (this.$route.query.folderId) {
                this.filter.folderId = this.$route.query.folderId;
            }

            this.form = this.appVars.settings.misc;

            this.logAlertInfo = window.localStorage.getItem('log-settings');

            if (!this.logAlertInfo) {
                window.localStorage.setItem('log-settings', JSON.stringify({
                    show_status_info: true,
                    show_status_warning: true
                }));
            }

            this.logAlertInfo = JSON.parse(window.localStorage.getItem('log-settings'));

            this.fetchFolders();
            this.fetchItems();
            
        },
        mounted() {
            // this.$nextTick(() => {
            //     this.renderNewPage();
            // });
            var vueInstance = this;
            new ClipboardJS('.copy_username', {
                text: function(trigger) {
                    console.log(trigger);
                    vueInstance.isPasswordConfirmationDialogVisible = true;
                    return trigger.getAttribute('data-username');
                }
            });
        }
    };
</script>
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
.dropdown_item{
    margin: 5px;
}
.danger{
    color: red;
}
</style>
