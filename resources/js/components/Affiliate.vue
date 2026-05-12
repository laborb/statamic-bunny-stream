<template>
    <ui-card class="mx-auto max-w-2xl text-center">
        <div class="flex flex-col items-center justify-center gap-3">
            <ui-heading size="lg" :text="__('Bunny Account Required')" />
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __("To use this addon, you'll need a Bunny.net account. Make sure to add your credentials to the .env file.") }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __("If you don't have one yet, you can sign up using our affiliate link.") }}</p>
            <ui-button href="https://bunny.net?ref=uhvsqhaw0n" target="_blank" variant="primary">
                {{ __('Sign Up Now') }}
            </ui-button>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __("By doing so, you'll be supporting the development of this addon at no extra cost to you.") }}</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Thank you for your support!') }}</p>
        </div>
    </ui-card>
</template>
