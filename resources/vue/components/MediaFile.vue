<template>
    <section class="medium">
        <header>
            {{ medium.title }}
        </header>
        <studip-icon shape="play" size="48" @click="playMedium"></studip-icon>
    </section>
</template>

<script>
    import StudipIcon from './StudipIcon'

    export default {
        name: 'MediaFile',
        components: {StudipIcon},
        props: {
            medium: {
                type: Object
            }
        },
        data() {
            return {
                playing: false
            }
        },
        methods: {
            playMedium: function() {
                window.open(this.medium.sharelink)
            }
        }
    }
</script>

<style lang="scss">
    .medium {
        border: 1px solid #28497c;
        float: left;
        height: 150px;
        margin: 5px;
        padding: 2px;
        text-align: center;
        width: 150px;

        header {
            background-color: #28497c;
            color: white;
            padding: 5px;
        }

        img, svg {
            margin-top: 30px;
        }
    }
</style>
