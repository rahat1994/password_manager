<template>
    <div>
        <div style="float:right;margin-right: 6px;">
            <el-button @click="isGeneralDialogVisible = true" type="info" size="small" >
                <i class="el-icon-plus" style="margin-right: 3px;"></i>{{$t('New')}}</el-button>
        </div> 

        <el-dialog
                title="Create"
                :visible.sync="isGeneralDialogVisible"
                width="50%"
                :close-on-click-modal="false"
            >
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
                            <el-button @click="isFolderCreationDialogVisible = true" type="primary" icon="el-icon-plus">Folder</el-button>
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

        <VaultItemCreationDialog
            :isItemCreationDialogVisible="isItemCreationDialogVisible"
            :folders="folders"
            @on-item-creation-dialog-closed="handleItemCreationDialogClosed"
        />

        <VaultFolderCreationDialog 
            :isVisible="isFolderCreationDialogVisible" 
            @on-folder-creation-dialog-closed="handleFolderCreationDialogClosed"    
        />
    </div>
</template>



<script>
import VaultFolderCreationDialog from './VaultFolderCreationDialog.vue';
import VaultItemCreationDialog from './VaultItemCreationDialog.vue';

export default {
    name: 'VaultHeaderButton',
    props: {
        folders: {
            type: Array,
            default: () => []
        }
    },
    components: {
        VaultFolderCreationDialog,
        VaultItemCreationDialog
    },
    data() {
        return {
            isGeneralDialogVisible: false,
            isItemCreationDialogVisible: false,
            isFolderCreationDialogVisible: false,
            isCollectionCreationDialogVisible: false,
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
            },
            formLabelWidth: '10rem'
        };
    },
    methods: {
      handleClose(done) {
        
      },
      openItemCreationDialog() {
        this.isItemCreationDialogVisible = true;
      },
      onFolderFormSubmit() {
        console.log('Folder form submitted');
      },
      onItemCreationFormSubmit() {
        console.log('Item form submitted');
      },
      handleFolderCreationDialogClosed({closeFolderCreationDialog}){
        console.log(closeFolderCreationDialog);
        this.isFolderCreationDialogVisible = false;
      },
      handleItemCreationDialogClosed({closeItemCreationDialog}){
        console.log(closeItemCreationDialog);
        this.isItemCreationDialogVisible = false;
      },

    },
    created: function () {
        console.log('VaultHeaderButton.vue created');
        console.log(this.folders);
    }
};
</script>