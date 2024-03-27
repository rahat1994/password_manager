<template>
    <el-dialog  
        :title="$t('Create New Folder')" 
        :visible.sync="isVisible"
        :before-close="onFolderCrreationDialogClosed" 
        :close-on-click-modal="false"
    >
        <el-form 
            ref="folderCreationForm" 
            :rules="folderCreationFormRules"  
            :model="folderCreationForm" style="padding:3rem">
            <el-form-item prop="name" :label="$t('Promotion name')" :label-width="formLabelWidth">
                <el-input v-model="folderCreationForm.name" autocomplete="off"></el-input>
            </el-form-item>

            <el-form-item style="float:right">
                <el-button type="primary" @click="onFolderFormSubmit('folderCreationForm')">Create</el-button>
                <el-button>Cancel</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</template>

<script>
export default{
    name: 'VaultFolderCreationDialog',
    props: ['isVisible'],
    data(){
        return {
            loading: false,
            debug_info: '',
            formLabelWidth: '10rem',
            folderCreationForm: {
                name: '',
                desc: ''
            },
            folderCreationFormRules: {
                name: [
                    { required: true, message: 'Please input the name', trigger: 'blur' },
                    { min: 3, max: 100, message: 'Length should be 3 to 100', trigger: 'blur' }
                ]
            },
        }
    },
    methods: {
        onFolderFormSubmit(formName){
            //this.$emit('on-folder-creation', this.folderCreationForm);
            //this.folderCreationForm.name = '';
            //this.folderCreationForm.desc = '';
            this.$refs[formName].validate((valid) => {
                console.log(valid);
                if (valid) {
                    console.log(this.folderCreationForm.name);
                    this.createFolder();
                } else {
                    return false;
                }
            });

        },
        createFolder(){

            this.loading = true;
            this.debug_info = '';

            this.$post('folder', { ...this.folderCreationForm }).then(res => {
                this.$notify.success({
                    title: 'Great!',
                    offset: 19,
                    message: res.data.message
                });
                this.$emit('on-folder-creation-dialog-closed', {closeFolderCreationDialog: true, fetchFolders:true});
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
        onFolderCrreationDialogClosed(){
            this.$emit('on-folder-creation-dialog-closed', {closeFolderCreationDialog: true});
        }
    }
}
</script>