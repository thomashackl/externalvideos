
<template>
    <a class="button" :class="[icon]" :href="href" :name="name" data-dialog-close>{{ label }}</a>
</template>

<script>
    export default {
        name: 'StudipLinkButton',
        props: {
            href: {
                type: String,
                default: ''
            },
            name: {
                type: String,
                default: ''
            },
            icon: {
                type: String,
                validator(value) {
                    return ['', 'accept', 'cancel', 'edit', 'move-up', 'move-down', 'add', 'download', 'search'].includes(value)
                },
                default: ''
            },
            label: {
                type: String
            }
        }
    }
</script>
