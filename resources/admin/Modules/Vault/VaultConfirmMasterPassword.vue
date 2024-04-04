<template>
    <el-dialog :title="$t('Confirm Master Password')" :visible.sync="isVisible" :before-close="onDialogClosed"
        :close-on-click-modal="false">
        <el-form ref="masterPasswordConfirmationForm" :rules="formRules" :model="form"
            style="padding:3rem">
            <el-form-item prop="name" :label="$t('Your Pasword')" :label-width="formLabelWidth">
                <el-input placeholder="Please input password" v-model="form.password" show-password></el-input>
            </el-form-item>

            <el-form-item style="float:right">
                <el-button type="primary" @click="onFormSubmit('masterPasswordConfirmationForm')">Confirm</el-button>
                <el-button @click="onDialogClosed">Cancel</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
</template>

<script>
export default {
    name: 'VaultConfirmMasterPassword',
    props: [
        'isVisible',
        'itemId'
    ],
    data() {
        return {
            loading: false,
            debug_info: '',
            formLabelWidth: '10rem',
            form: {
                password: ''
            },
            formRules: {
                password: [
                    { required: true, message: 'Please input the password', trigger: 'blur' },
                ]
            },
        }
    },
    methods: {
        onFormSubmit(formName) {
            
            this.$refs[formName].validate((valid) => {
                console.log(valid);
                if (valid) {
                    this.verifyPassword();
                } else {
                    return false;
                }
            });

        },
        verifyPassword() {

            this.loading = true;
            this.debug_info = '';
            console.log(this.itemId);
            var formData = {
                password: this.form.password,
                itemId: this.itemId
            };

            this.$post('validate-master-password', 
                        formData
                    ).then(res => {

                        if (res.success === true) {
                            this.$notify.success({
                                title: 'Great!',
                                offset: 19,
                                message: res.data.message
                            });
                            this.$emit('on-master-pass-confirmation-dialog-closed', { success: res.success, item: res.data});
                        } else {
                            this.$notify.error({
                                title: 'Oops!',
                                offset: 19,
                                message: res.data.message
                            });
                        }
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
                this.password = "";
            });
        },
        onDialogClosed() {
            this.password = "";
            this.$emit('on-master-pass-confirmation-dialog-closed', { closeFolderCreationDialog: true });
        }
    },
    created:()=>{
    }
}
</script>