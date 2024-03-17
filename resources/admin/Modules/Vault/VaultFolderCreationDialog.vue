<template>
    <el-dialog  
        :title="$t('Create New Folder')" 
        :visible.sync="isVisible"
        :before-close="onFolderCrreationDialogClosed"   
    >
        <el-form size="mini" :rules="folderCreationFormRules"  :model="folderCreationForm">
            <el-form-item :label="$t('Promotion name')" :label-width="formLabelWidth">
                <el-input v-model="folderCreationForm.name" autocomplete="off"></el-input>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="onFolderFormSubmit">Create</el-button>
                <el-button>Cancel</el-button>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">Cancel</el-button>
            <el-button type="primary" @click="dialogVisible = false">Confirm</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default{
    name: 'VaultFolderCreationDialog',
    props: ['isVisible'],
    data(){
        return {
            formLabelWidth: '10rem',
            folderCreationForm: {
                name: '',
                desc: ''
            },
            folderCreationFormRules: {
                name: [
                    { required: true, message: 'Please input the name', trigger: 'blur' }
                ]
            },
        }
    },
    methods: {
        onFolderFormSubmit(){
            this.$emit('on-folder-creation', this.folderCreationForm);
            this.folderCreationForm.name = '';
            this.folderCreationForm.desc = '';
        },
        onFolderCrreationDialogClosed(){
            this.$emit('on-folder-creation-dialog-closed', {closeFolderCreationDialog: true});
        }
    }
}
</script>