<template>
    <el-dialog :title="(context === 'carete_item')?$t('Create New Item'):$t('Edit Item')" :visible.sync="isItemCreationDialogVisible"
        :before-close="onItemCreationFormClose" :close-on-click-modal="false">
        <span>
            <el-form ref="itemCreationForm" size="mini" :rules="itemCreationFormRules" :model="form" label-width="11rem"
                style="padding:2rem">

                <el-form-item :label="$t('Item Type')" prop="itemType">
                    <el-col :span="24">
                        <el-select v-model="form.itemType">
                            <el-option label="Login" value="login"></el-option>
                            <el-option label="Card" value="card"></el-option>
                            <el-option label="Identity" value="identity"></el-option>
                            <el-option label="Secure note" value="secure_note"></el-option>
                        </el-select>
                    </el-col>

                </el-form-item>

                <el-form-item :label="$t('Name & Folder')" required>
                    <el-col :span="12">
                        <el-form-item prop="name">
                            <el-input :placeholder="$t('Name')" v-model="form.name" style="width: 100%;"></el-input>
                        </el-form-item>
                    </el-col>

                    <el-col :span="12">
                        <el-form-item prop="folder">
                            <el-select v-model="form.folder" :placeholder="$t('Folder')">
                                <el-option v-for="folder in folders" :key="folder.id" :label="folder.name"
                                    :value="folder.id"></el-option>
                            </el-select>
                        </el-form-item>

                    </el-col>
                </el-form-item>

                <el-form-item :label="$t('Username & password')" required>
                    <el-col :span="12">

                        <el-form-item prop="username">
                            <el-input clearable id="item_username" size="small" v-model="form.username"
                                @clear="form.username=''" @keyup.enter.native="fetch" :placeholder="$t('Username')">
                                <el-button class="copy_item_username" data-clipboard-target="#item_username" style="width: 3rem;"
                                    slot="append" icon="el-icon-document-copy" />
                            </el-input>
                        </el-form-item>

                    </el-col>

                    <el-col :span="12">

                        <el-form-item prop="password">
                            <el-input size="small" v-model="form.password" show-password="true"
                                :placeholder="$t('password')">
                                <el-button style="width: 3rem;" slot="append" icon="el-icon-document-copy"
                                    @click="()=>{}" />
                            </el-input>
                        </el-form-item>


                    </el-col>
                </el-form-item>

                <el-form-item :label="$t('URL')" prop="url">
                    <el-col :span="24">

                        <el-input clearable size="small" v-model="form.url" @clear="form.url=''"
                            :placeholder="$t('URL')">
                            <el-button style="width: 3rem;" slot="append" icon="el-icon-top-right" @click="() => openWindow(form.url)" />
                        </el-input>
                    </el-col>
                </el-form-item>

                <el-form-item :label="$t('Description')" prop="desc">
                    <el-col>
                        <el-input type="textarea" v-model="form.desc"></el-input>
                    </el-col>

                </el-form-item>

                <el-divider></el-divider>

                <div style="margin: 8px 0px;padding: 2px 5px;">
                    <span>Protected by masterpass</span>
                    <el-switch v-model="form.masterPassProtected"></el-switch>
                </div>
                <el-form-item style="float:right">
                    <el-button type="primary" @click="onItemCreationFormSubmit('itemCreationForm')">Create</el-button>
                    <el-button>Cancel</el-button>
                </el-form-item>
            </el-form>
        </span>
    </el-dialog>
</template>

<script>

import ClipboardJS from 'clipboard';
export default {
    name: "VaultItemCreationDialog",
    props: {
        isItemCreationDialogVisible: {
            type: Boolean,
            default: false
        },
        folders: {
            type: Array,
            default: () => []
        },
        context: {
            type: String,
            default: 'create_item'
        },
        item_id: {
            type: Number,
            default: null
        },
        form: {
            type: Object,
            default: () => {
                return {
                    itemType: '',
                    name: '',
                    folder: '',
                    username: '',
                    password: '',
                    url: '',
                    desc: '',
                    masterPassProtected: false
                }
            }
        }
    },
    data() {
        return {
            itemCreationFormRules:{
                itemType: [
                    { required: true, message: 'Please select the item type', trigger: 'blur' }
                ],
                name: [
                    { required: true, message: 'Please input the name', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ],
                folder: [
                    { required: true, message: 'Please select the folder', trigger: 'blur' }
                ],
                username: [
                    { required: true, message: 'Please input the username', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ],
                password: [
                    { required: true, message: 'Please input the password', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ],
                url: [
                    { required: true, message: 'Please input the url', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ],
                desc: [
                    { required: false, message: 'Please input the description', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ]
            
            }
        }
    },
    methods: {
        onItemCreationFormSubmit(formName){
            this.$refs[formName].validate((valid) => {
                console.log(valid);
                if (valid) {
                    console.log(this.$refs[formName]);
                    this.createItem();
                } else {
                    return false;
                }
            });

        },
        openWindow(url){
            window.open(url, '_blank');
        },
        onItemCreationFormClose() {
            this.$emit('on-item-creation-dialog-closed', {closeItemCreationDialog: true});
        },
        createItem(){

            this.loading = true;
            this.debug_info = '';

            if(this.context == 'edit_item'){
                this.$post('item/update', { 
                    item_id: this.item_id,
                    ...this.form 
                }).then(res => {
                    this.$notify.success({
                        title: 'Great!',
                        offset: 19,
                        message: res.data.message
                    });
                    // clear the form
                    this.form = {
                        itemType: '',
                        name: '',
                        folder: '',
                        username: '',
                        password: '',
                        url: '',
                        desc: '',
                        masterPassProtected: false
                    };
                    this.$emit('on-item-creation-dialog-closed', {closeItemCreationDialog: true, refreshItems: true});
                }).fail(res => {
                    if (Number(res.status) === 504) {
                        return this.$notify.error({
                            title: 'Oops!',
                            offset: 19,
                            message: '504 Gateway Time-out.'
                        });
                    } else if(Number(res.status) === 422){
                        const responseJSON = res.responseJSON;
                        
                        return this.$notify.error({
                            title: 'Oops!',
                            offset: 19,
                            message: res.data.message
                        });
                    }
                }).always(() => {
                    this.loading = false;
                });
                return;
            }
            this.$post('item', { ...this.form }).then(res => {
                this.$notify.success({
                    title: 'Great!',
                    offset: 19,
                    message: res.data.message
                });
                // clear the form
                this.form = {
                    itemType: '',
                    name: '',
                    folder: '',
                    username: '',
                    password: '',
                    url: '',
                    desc: '',
                    masterPassProtected: false
                };
                this.$emit('on-item-creation-dialog-closed', {
                    closeItemCreationDialog: true,
                    refreshItems: true
                });
            }).fail(res => {
                if (Number(res.status) === 504) {
                    return this.$notify.error({
                        title: 'Oops!',
                        offset: 19,
                        message: '504 Gateway Time-out.'
                    });
                } else if(Number(res.status) === 422){
                    const responseJSON = res.responseJSON;
                    
                    return this.$notify.error({
                        title: 'Oops!',
                        offset: 19,
                        message: res.data.message
                    });
                }
            }).always(() => {
                this.loading = false;
            });
        },

    }, 
    created: function(){
        var itemUsernameCopyButton = new ClipboardJS('.copy_item_username', {
            text: function(trigger) {

                console.log(trigger);
                return "This is a copy text";
            }        
        });
        
        itemUsernameCopyButton.on('success', function(e) {
            // e.clearSelection();
            // this.$notify.success({
            //     title: 'Great!',
            //     offset: 19,
            //     message: 'Username copied to clipboard'
            // });
        });
        console.log(this.form);
        console.log(this.context);
    }
}
</script>
<style>
.el-result{
    text-align: center;
}
.el-result svg{
    width: 100px;
    height: 100px;
}
</style>