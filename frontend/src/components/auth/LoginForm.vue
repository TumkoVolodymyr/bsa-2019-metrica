<template>
    <div class="form">
        <h3 class="title mb-8 mt-6">
            Welcome to Metrica!
        </h3>
        <VForm
            lazy-validation
            ref="form"
            v-model="valid"
        >
            <label
                class="caption grey--text"
            >
                Email
            </label>
            <VTextField
                class="no-underline mt-1"
                solo
                type="email"
                name="email"
                v-model="email"
                :rules="emailRules"
                required
            />
            <label
                class="caption grey--text"
            >
                Password
            </label>
            <VTextField
                class="no-underline password mt-1"
                solo
                name="password"
                autocomplete="new-password"
                v-model="password"
                :append-icon="showPassword ? 'visibility' : 'visibility_off'"
                :rules="passwordRules"
                :type="showPassword ? 'text' : 'password'"
                @click:append="showPassword = !showPassword"
                required
            />

            <div class="password-group">
                <div class="btn-group mt-8 mb-3">
                    <VBtn
                        class="login-btn"
                        min-width="100px"
                        color="primary"
                        :disabled="!valid"
                        @click="onLogin"
                    >
                        {{ signInText }}
                    </VBtn>
                    <VBtn
                        class="start"
                        min-width="100px"
                        :to="{name: 'signup'}"
                        outlined
                        :disabled="false"
                    >
                        SIGN UP
                    </VBtn>
                </div>
                <div>
                    <RouterLink
                        class="forgot-password-link"
                        :to="{name: 'reset-password'}"
                    >
                        Forgot password?
                    </RouterLink>
                </div>
            </div>
        </VForm>

        <SocialAuth
            class="mt-4"
        />
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import {LOGIN} from "@/store/modules/auth/types/actions";
    import {SHOW_SUCCESS_MESSAGE, SHOW_ERROR_MESSAGE} from "@/store/modules/notification/types/actions";
    import {validateEmail} from '@/services/validation';
    import {validatePassword} from '@/services/validation';
    import SocialAuth from './SocialAuth';

    export default {
        components: {
            SocialAuth
        },

        data() {
            return {
                email: '',
                password: '',
                showPassword: false,
                valid: false,
                isLoading: false,
                emailRules: [
                    v => !!v || 'E-mail is required',
                    v => validateEmail(v) || 'E-mail must be valid',
                ],
                passwordRules: [
                    v => !!v || 'Password is required',
                    v => validatePassword(v) || 'Password must be equal or more than 8 characters'
                ]
            };
        },
        methods: {
            ...mapActions('auth', {
                login: LOGIN
            }),
            ...mapActions('notification', {
                showSuccessMessage: SHOW_SUCCESS_MESSAGE,
                showErrorMessage: SHOW_ERROR_MESSAGE
            }),
            onLogin() {
                if (this.$refs.form.validate()) {
                    this.isLoading = true;
                    this.login({
                        email: this.email,
                        password: this.password
                    }).then(() => {
                        this.$emit("success");
                        this.showSuccessMessage('Logged in');
                    }).catch((error) => {
                        this.showErrorMessage(error);
                    }).finally(() => {
                        this.isLoading = false;
                    });
                }
            }
        },
        computed: {
            signInText() {
                return this.isLoading ? 'Processing...' : 'SIGN IN';
            }
        }
    };
</script>

<style lang="scss" scoped>
    .form {
        width: 50%;

        .v-btn {
            text-transform: none;

            + .start {
                background: #FFFFFF;
                color: #3C57DE;
                margin-left: 50px;
            }
        }

        .password-group {
            display: flex;
            flex-direction: column;
        }

        .btn-group {
            display: flex;
            justify-content: flex-start;
            .v-btn:last-child {
                background-color: transparent;
            }
        }
    }
</style>
