<template>
    <div>
        <div style="float:right;margin-right: 6px;">
            <el-button @click="isGeneralDialogVisible = true" type="info" size="small" >
                <i class="el-icon-plus" style="margin-right: 3px;"></i>{{$t('New')}}</el-button>
        </div> 

        <el-dialog
            title="Create"
            :visible.sync="isGeneralDialogVisible"
            width="30%">
            <span>
                <el-row>
                    <el-col :sm="12" :lg="8">
                        <el-result icon="info" title="Success Tip" subTitle="Please follow the instructions">
                        <template slot="extra">
                            <el-button @click="isItemCreationDialogVisible = true" type="primary" icon="el-icon-plus">Item</el-button>
                        </template>
                        </el-result>
                    </el-col>
                    <el-col :sm="12" :lg="8">
                        <el-result icon="info" title="Warning Tip" subTitle="Please follow the instructions">
                        <template slot="extra">
                            <el-button type="primary" icon="el-icon-plus">Folder</el-button>
                        </template>
                        </el-result>
                    </el-col>
                    <el-col :sm="12" :lg="8">
                        <el-result icon="info" title="Error Tip" subTitle="Please follow the instructions">
                        <template slot="extra">
                            <el-button type="primary" icon="el-icon-plus">Collection</el-button>
                        </template>
                        </el-result>
                    </el-col>
                </el-row>
            </span>
            <span slot="footer" class="dialog-footer">
                <el-button @click="isGeneralDialogVisible = false">Cancel</el-button>
                <el-button type="primary" @click="dialogVisible = false">Confirm</el-button>
            </span>
        </el-dialog>

        <el-dialog :title="$t('Create New Item')" :visible.sync="isItemCreationDialogVisible">
            <span>
                <el-form ref="form" size="mini" :model="form" label-width="120px">

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
                            <el-input placeholder="Name" v-model="form.name" style="width: 100%;"></el-input>
                        </el-col>

                        <el-col :span="12">
                            <el-select v-model="form.folder">
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
                        <el-col>
                            <el-input  placeholder="URL" v-model="form.url" style="width: 100%;"></el-input>    
                        </el-col>                                                
                    </el-form-item>

                    <el-form-item label="Activity form">
                        <el-input type="textarea" v-model="form.desc"></el-input>
                    </el-form-item>
                    <el-divider></el-divider>

                    <div style="margin: 8px 0px;padding: 2px 5px;">
                        <span>Protected by masterpass</span>
                        <el-switch v-model="form.delivery"></el-switch>
                    </div>
                    <el-form-item>
                        <el-button type="primary" @click="onSubmit">Create</el-button>
                        <el-button>Cancel</el-button>
                    </el-form-item>
                    </el-form>
            </span>
            <span slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">Cancel</el-button>
                <el-button type="primary" @click="dialogFormVisible = false">Confirm</el-button>
            </span>
        </el-dialog>

    </div>
</template>



<script>
export default {
    name: 'VaultHeaderButton',
    props: ['selected'],
    data() {
        return {
            isGeneralDialogVisible: false,
            isItemCreationDialogVisible: false,
            isFolderCreationDialogVisible: false,
            isCollectionCreationDialogVisible: false,
            folders: [
                {
                    id: 1,
                    name: 'Pass 2024',
                    value: ''
                },
                {
                    id: 2,
                    name: 'Pass 2023',
                    value: ''
                }
            ],
            form: {
                name: '',
                item_type: '',
                folder: null,
                username: '',
                password: '',
                delivery: false,
                type: [],
                url: '',
                desc: ''
            }
        };
    },
    methods: {
      handleClose(done) {
        
      },
      openItemCreationDialog() {
        this.isItemCreationDialogVisible = true;
      }
    },
};
</script>