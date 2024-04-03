<template>
    <div v-if="!loadingItems" class="content" style="background-color: #f5f7fa;">
        <div>
            <h1>Tools</h1>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'Tools',
        data() {
            return {
                loading: false,
            }
        },
    }
</script>