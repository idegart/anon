<template>
    <div>
        <v-stepper v-model="step" vertical dark class="pa-5" style="border-radius: 0; box-shadow: none;">
            <v-stepper-step :complete="step > 1" step="1">Создайте бота</v-stepper-step>
            <v-stepper-content step="1" class="pt-0">
                <section>
                    Перейдите в Telegram, выберите <a href="https://telegram.me/BotFather" target="_blank">@BotFather</a>.
                    Следуйте подсказкам BotFather для создания своего нового бота
                </section>
                <div class="mt-3">
                    <v-btn color="primary" @click="step = 2" small>Продолжить</v-btn>
                </div>
            </v-stepper-content>

            <v-stepper-step :complete="step > 2" step="2">Укажите настройки бота</v-stepper-step>
            <v-stepper-content step="2" class="pt-0">
                <section>
                    <p>Введите токен который был сгенерирован при создании бота</p>

                    <v-alert v-if="formToken.error" type="error">
                        Что-то пошло не так, попробуйте еще раз
                    </v-alert>

                    <v-form v-model="formToken.valid">
                        <v-text-field
                            v-model="formToken.value"
                            :rules="formToken.rules"
                            label="Token"
                            required
                            outlined
                            placeholder="123456789:AABBCCDDEEFFGGHHIIJJKKLL"
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
                    <v-btn @click="createBot"
                           :disabled="!(formToken.valid && formToken.captchaResponse || true)"
                           :loading="formToken.loading"
                           color="primary"
                           small>Продолжить</v-btn>
                </div>
            </v-stepper-content>

            <v-stepper-step :complete="step > 3" step="3">Добавьте бота в чат</v-stepper-step>
            <v-stepper-content step="3" class="pt-0">
                <section>
                    <p>Перейдите в необходимый чат и добавьте своего бота.</p>
                    <p>После того как бот уже добавлен, введите команду <kbd>/start-anon</kbd></p>
                </section>
                <div class="mt-3">
                    <v-btn color="primary" @click="step = 4" small>Продолжить</v-btn>
                </div>
            </v-stepper-content>

            <v-stepper-step step="4">Начните общение</v-stepper-step>
            <v-stepper-content step="4" class="pt-0">
                <section>
                    Напишите личное сообщение.
                    Бот в скором времени отправит его от своего имени в общий чат
                </section>
                <div class="mt-3">
                    <v-btn color="green" @click="step = 1" small>Хочу еще!</v-btn>
                </div>
            </v-stepper-content>
        </v-stepper>
    </div>
</template>

<script>
    import vueRecaptcha from 'vue-recaptcha'

    export default {
        name: "CreateBotStepper",
        components: {vueRecaptcha},
        data: () => ({
            step: 1,

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
                ],
            },
        }),

        computed: {
            recaptchaKey () {
                return process.env.MIX_RECAPTCHA_KEY_SITE
            },
        },

        methods: {
            verifyCaptcha (response) {
                this.formToken.captchaResponse = response
            },
            expiredCaptcha () {
                this.formToken.captchaResponse = null
            },

            createBot () {
                this.formToken.loading = true
                this.$http.post('/api/bot', {
                    'g-recaptcha-response': this.formToken.captchaResponse,
                    token: this.formToken.value,
                })
                    .then(({data}) => {
                        if (data.success) {
                            this.formToken.success = true
                            this.step = 3
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
