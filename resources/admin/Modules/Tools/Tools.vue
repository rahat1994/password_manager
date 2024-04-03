<template>
    <div v-if="!loading" class="content" style="background-color: #f5f7fa; padding: 20px 10px;">
        <div>
            <h1>{{ $t("Generator") }}</h1>
        </div>

        <div>
            {{ generatePassword }}
        </div>

        <div>
            <el-form ref="form" :model="form" label-width="10rem">

                <el-form-item label="Options">
                    <el-checkbox-group v-model="form.type">
                        <el-checkbox label="A-Z" name="type"></el-checkbox>
                        <el-checkbox label="a-z" name="type"></el-checkbox>
                        <el-checkbox label="0-9" name="type"></el-checkbox>
                        <el-checkbox label="!@#$%^&*" name="type"></el-checkbox>
                    </el-checkbox-group>
                </el-form-item>

                <el-form-item label="Length">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>

                <el-form-item label="Minimum Numbers">
                    <el-input v-model="form.minimumNumbers"></el-input>
                </el-form-item>

                <el-form-item label="Minimum Special">
                    <el-input v-model="form.minimumSpecials"></el-input>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="onSubmit">Regenerate Password</el-button>
                    <el-button>Cancel</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'Tools',
        data() {
            return {
                loading: false,
                generatePassword: "G3Ed8n%oVn",
                form: {
                    name: '',
                    region: '',
                    date1: '',
                    date2: '',
                    delivery: false,
                    type: [],
                    resource: '',
                    desc: ''
                }
            }
        },
    }
</script>