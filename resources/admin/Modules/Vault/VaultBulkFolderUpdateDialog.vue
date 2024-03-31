<template>
    <el-dialog :title="$t('Bulk Update')" :visible.sync="isVisible" :before-close="onFolderUpdateDialogClosed"
        :close-on-click-modal="false">
        <el-form ref="folderUpdateForm" :rules="formRules" :model="form" style="padding:3rem">
            <el-form-item prop="folderId" :label="$t('Select folder to move')" :label-width="formLabelWidth">
                <el-select v-model="form.folderId" placeholder="Select a folder" clearable>
                    <el-option v-for="folder in folders" :key="folder.id" :label="folder.name" :value="folder.id">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item style="float:right">
                <el-button type="primary" @click="onFolderUpdateFormSubmit('folderUpdateForm')">{{ $t('Confirm')
                    }}</el-button>
                <el-button @click="onFolderUpdateDialogClosed">{{ $t('Cancel') }}</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</template>

<script>
export default {
    name: 'VaultFolderCreationDialog',
    props: [
        'isVisible',
        'folders',
        'selectedItems'
    ],
    data() {
        return {
            loading: false,
            debug_info: '',
            formLabelWidth: '10rem',
            form: {
                folderId: null,
            },
            formRules: {
                folderId: [
                    { required: true, message: 'Please select a folder', trigger: 'change' }
                ],
            },
        }
    },
    methods: {
        onFolderUpdateFormSubmit(formName) {
            this.$refs[formName].validate((valid) => {                
                if (valid) {
                    console.log(this.form.selectedFodler);
                    this.updateItems();
                } else {
                    return false;
                }
            });

        },
        updateItems() {

            this.loading = true;
            this.debug_info = '';
            console.log(this.selectedItems.map(item => item.id));
            var data = {
                folderId: this.form.folderId,
                itemId: this.selectedItems.map(item => item.id)
            };
            this.$post('item/bulk-move', { ...data }).then(res => {
                this.$notify.success({
                    title: 'Great!',
                    offset: 19,
                    message: res.data.message
                });
                this.$emit('on-folder-creation-dialog-closed', { closeFolderCreationDialog: true, fetchFolders: true });
            }).fail(res => {
                if (Number(res.status) === 504) {
                    return this.$notify.error({
                        title: 'Oops!',
                        offset: 19,
                        message: '504 Gateway Time-out.'
                    });
                } else if (Number(res.status) === 422) {
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
        onFolderUpdateDialogClosed() {
            this.$emit('on-folder-update-dialog-closed', { closeFolderCreationDialog: true });
        }
    }
}
</script>