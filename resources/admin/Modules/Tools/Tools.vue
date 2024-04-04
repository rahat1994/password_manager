<template>
    <div v-if="!loading" class="content" style="background-color: #f5f7fa; padding: 20px 10px;">
        <div>
            <h1>{{ $t("Generator") }}</h1>
        </div>

        <div class="generated_pass_section">
            {{ password }}
        </div>

        <div>
            <el-form ref="form" :model="form" label-width="10rem">

                <el-form-item label="Options">
                    
                    <el-checkbox label="A-Z" v-model="form.useUppercase"></el-checkbox>
                    <el-checkbox label="0-9" v-model="form.useNumbers"></el-checkbox>
                    <el-checkbox label="!@#$%^&*()_+~`|}{[]:;?><,./-=" v-model="form.useSymbols"></el-checkbox>
                    
                </el-form-item>

                <el-form-item label="Length">
                    <el-input max="50" v-model="form.length" type="number"></el-input>
                </el-form-item>

                <el-form-item label="Minimum Numbers">
                    <el-input max="50" v-model="form.minimumNumbers" type="number"></el-input>
                </el-form-item>

                <el-form-item label="Minimum Special">
                    <el-input max="50" v-model="form.minimumSpecials" type="number"></el-input>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="regeneratePassword">Regenerate Password</el-button>
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
                    length: 10,
                    minimumNumbers: 1,
                    minimumSpecials: 1,
                    useUppercase: true,
                    useNumbers: true,
                    useSymbols:true,                 
                },

            }
        },
        methods:{
            regeneratePassword(){

                let charset = "abcdefghijklmnopqrstuvwxyz";
                if (this.form.useUppercase) charset += "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                
                let numbers = "0123456789";
                let symbols = "!@#$%^&*()_+~`|}{[]:;?><,./-=";
                
                let password = "";
                
                // Add minimum numbers
                if (this.form.useNumbers && this.form.minimumNumbers > 0) {
                    for (let i = 0; i < this.form.minimumNumbers; i++) {
                    let at = Math.floor(Math.random() * numbers.length);
                    password += numbers.charAt(at);
                    }
                    charset += numbers;
                }
                
                // Add minimum symbols
                if (this.form.useSymbols && this.form.minimumSpecials > 0) {
                    for (let i = 0; i < this.form.minimumSpecials; i++) {
                    let at = Math.floor(Math.random() * symbols.length);
                    password += symbols.charAt(at);
                    }
                    charset += symbols;
                }
                
                // Fill the rest of the password with random characters from the charset
                for (let i = password.length; i < this.form.length; i++) {
                    let at = Math.floor(Math.random() * charset.length);
                    password += charset.charAt(at);
                }
                
                // Shuffle the password to ensure the numbers and symbols are not just at the beginning
                password = password.split('').sort(() => 0.5 - Math.random()).join('');
                
                this.password = password;

            }
        },
        computed: {
            password:{
                get(){
                    return this.generatePassword;
                },
                set(value){
                    this.generatePassword = value;
                }            
            }
        }, 
        created(){
            this.regeneratePassword();
        }

    }
</script>

<style>
.generated_pass_section{
    text-align: center;
    font-size: 3rem;
    padding: 40px 31px;
    border: 1px solid black;
    margin: 21px 0px;
    border-radius: 5px;
}
</style>