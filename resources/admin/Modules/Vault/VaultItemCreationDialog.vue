<template>
    <el-dialog 
        :title="$t('Create New Item')" 
        :visible.sync="isItemCreationDialogVisible"
        :close-on-click-modal="false"
        >
        <span>
            <el-form ref="form" size="mini" :model="form" label-width="10rem" style="padding:3rem">

                <el-form-item :label="$t('Item Type')">
                    <el-col :span="24">
                        <el-select v-model="form.item_type">
                            <el-option label="Login" value="login"></el-option>
                            <el-option label="Card" value="card"></el-option>
                            <el-option label="Identity" value="identity"></el-option>
                            <el-option label="Secure note" value="secure_note"></el-option>
                        </el-select>
                    </el-col>

                </el-form-item>

                <el-form-item :label="$t('Name & Folder')">
                    <el-col :span="12">
                        <el-input :placeholder="$t('Name')" v-model="form.name" style="width: 100%;"></el-input>
                    </el-col>

                    <el-col :span="12" >
                        <el-select v-model="form.folder" :placeholder="$t('Folder')">
                            <el-option v-for="folder in folders" :key="folder.id" :label="folder.name" :value="folder.name"></el-option> 
                        </el-select>
                    </el-col>
                </el-form-item> 
                
                <el-form-item :label="$t('Username & password')">
                    <el-col :span="12">

                        <el-input
                            clearable
                            size="small"
                            v-model="form.username"
                            @clear="form.username=''"
                            @keyup.enter.native="fetch"
                            :placeholder="$t('Type & press enter...')"
                        >
                            <el-button style="width: 3rem;" slot="append" icon="el-icon-document-copy" @click="()=>{}"/>
                        </el-input>
                    </el-col>

                    <el-col :span="12">
                        <el-input
                            clearable
                            size="small"
                            v-model="form.password"
                            show-password
                            :placeholder="$t('password')"
                        >
                            <el-button style="width: 3rem;" slot="append" icon="el-icon-document-copy" @click="()=>{}"/>
                        </el-input>
                    </el-col>
                </el-form-item>

                <el-form-item :label="$t('URL')">
                    <el-col :span="24">

                        <el-input
                            clearable
                            size="small"
                            v-model="form.url"
                            @clear="form.url=''"
                            :placeholder="$t('URL')"
                        >
                            <el-button style="width: 3rem;" slot="append" icon="el-icon-top-right" @click="()=>{}"/>
                        </el-input>
                    </el-col>
                </el-form-item>

                <el-form-item :label="$t('Description')">
                    <el-col>
                        <el-input type="textarea" v-model="form.desc"></el-input>
                    </el-col>
                    
                </el-form-item>

                <el-divider></el-divider>

                <div style="margin: 8px 0px;padding: 2px 5px;">
                    <span>Protected by masterpass</span>
                    <el-switch v-model="form.delivery"></el-switch>
                </div>
                <el-form-item style="float:right">
                    <el-button type="primary" @click="onItemCreationFormSubmit">Create</el-button>
                    <el-button>Cancel</el-button>
                </el-form-item>
            </el-form>
        </span>
    </el-dialog>
</template>

<script>
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
        }
    },
    data() {
        return {
            form: {
                item_type: '',
                name: '',
                folder: '',
                username: '',
                password: '',
                url: '',
                desc: '',
                delivery: false
            },
        }
    },
    methods: {
        onItemCreationFormSubmit() {
            console.log(this.form);
        },
        onItemCreationFormClose() {
            this.$emit('on-item-creation-dialog-closed', {closeItemCreationDialog: true});
        }
    }
}
</script>