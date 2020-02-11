<template>
    <v-app dark>
        <v-content>
            <v-container class="fill-height" fluid>
                <v-row align="center" justify="center">
                    <v-col cols="12" sm="8" md="4">
                        <v-card class="elevation-12" shaped dark>
                            <v-toolbar color="grey darken-4" dark flat class="text-center">
                                <v-toolbar-title>{{ appName }}</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text class="pa-0">
                                <create-bot-stepper />
                            </v-card-text>
                            <v-card-text class="pa-0">
                                <v-expansion-panels flat focusable accordion>
                                    <v-expansion-panel>
                                        <v-expansion-panel-header>Что это такое?</v-expansion-panel-header>
                                        <v-expansion-panel-content class="pt-3">
                                            <p>
                                                <b>Анонимный чат с темами для обсуждения деликатных офисных <small>(и не только)</small> проблем.</b>
                                            </p>
                                            <p>
                                                Иногда хочется обсудить что-то с коллегами в офисе, но не хочется смущать их или показывать лишнюю инициативу.
                                                Например кто-то не смывает в туалете или слишком громко орёт и сам того не замечает. Может быть кто-то слишком интенсивно пользуется парфюмом.
                                            </p>
                                            <p>
                                                Данный сервис позволит Вам создать бота, который будет проксировать Ваши сообщения и посылать их от своего имени в общий чат.
                                            </p>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                    <v-expansion-panel>
                                        <v-expansion-panel-header>Вы следите за нами?</v-expansion-panel-header>
                                        <v-expansion-panel-content class="pt-3">
                                            Нет, бот <b>не сохраняет сообщения</b>! Он лишь копирует Ваше сообщение и отправляет его от своего имени.
                                            Таким образом, мы не храним Ваши сообщения и никто не сможет узнать кто же их отправляет
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                    <v-expansion-panel>
                                        <v-expansion-panel-header>Я устал от анонизма</v-expansion-panel-header>
                                        <v-expansion-panel-content class="pt-3">
                                            <section>
                                                <p>Введите token, который Вы указывали при создании Бота, и мы отключим его от нашей системы</p>

                                                <v-alert v-if="formToken.success" type="success">
                                                    Бот отключен
                                                </v-alert>

                                                <v-alert v-if="formToken.error" type="error">
                                                    Что-то пошло не так, попробуйте еще раз
                                                </v-alert>

                                                <v-form v-model="formToken.valid">
                                                    <v-text-field
                                                        v-model="formToken.value"
                                                        :rules="formToken.rules"
                                                        label="Token"
                                                        placeholder="123456789:AABBCCDDEEFFGGHHIIJJKKLL"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </v-form>
                                            </section>

                                            <div>
                                                <vue-recaptcha ref="recaptcha"
                                                               :sitekey="recaptchaKey"
                                                               :loadRecaptchaScript="true"
                                                               @verify="verifyCaptcha"
                                                               @expired="expiredCaptcha"
                                                               theme="dark" />
                                            </div>

                                            <div class="mt-3">
                                                <v-btn :disabled="!(formToken.valid && formToken.captchaResponse || true)"
                                                       :loading="formToken.loading"
                                                       color="error"
                                                       @click="destroyBot"
                                                       small>Продолжить</v-btn>
                                            </div>
                                        </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    import vueRecaptcha from 'vue-recaptcha'
    import CreateBotStepper from "../components/CreateBotStepper";

    export default {
        name: "Main",

        components: {vueRecaptcha, CreateBotStepper},

        data: () => ({
            formToken: {
                value: null,
                valid: false,
                error: false,
                success: false,
                loading: false,
                captchaResponse: null,
                rules: [
                    v => !!v || 'Обязательное поле',
                    v => /[0-9]{9}:[a-zA-Z0-9_-]{35}/.test(v) || 'Невалидный токен'
                ]
            },
        }),

        computed: {
            appName () {
                return process.env.MIX_APP_NAME
            },
            recaptchaKey () {
                return process.env.MIX_RECAPTCHA_KEY_SITE
            }
        },

        methods: {
            verifyCaptcha (response) {
                this.formToken.captchaResponse = response
            },
            expiredCaptcha () {
                this.formToken.captchaResponse = null
            },

            destroyBot () {
                this.formToken.loading = true
                this.formToken.error = false
                this.formToken.success = false
                this.$http.delete(`/api/bot/${this.formToken.value}`, {
                    data: {
                        'g-recaptcha-response': this.formToken.captchaResponse,
                    }
                })
                    .then(({data}) => {
                        if (data.success) {
                            this.formToken.success = true
                        } else {
                            this.formToken.error = true
                        }
                    })
                    .catch(() => {
                        this.formToken.error = true
                    })
                    .finally(() => {
                        this.$refs.recaptcha.reset()
                        this.formToken.loading = false
                        this.formToken.value = null
                    })
            },
        }
    }
</script>

<style scoped>

</style>
