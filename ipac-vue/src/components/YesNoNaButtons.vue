<template>

	<div class="btn-group">
		<button type="button" v-on:click="input('yes')" :class="{active: activeValue=='yes'}" class="btn btn-default btn-yes">Yes</button>
		<button type="button" v-on:click="input('no')" :class="{active: activeValue=='no'}" class="btn btn-default btn-no">No</button>
		<button type="button" v-on:click="input('n/a')" :class="{active: activeValue=='n/a'}" class="btn btn-default">N/A</button>
	</div>

</template>

<script type="text/javascript">

	export default {
		name: "YesNoNaButtons",
		props: ["value"],
		data() {
			return {
				activeValue: this.value
			}
		},
		methods: {
			input(value) {
				this.activeValue = value
				this.$emit("input",value)
				this.$emit("change",value)
			}
		}
	}
</script>

<style type="text/css" scoped>
	.btn-group .btn-yes.active {
		background-color: #9ee890;
	}

	.btn-group .btn-no.active {
		background-color: #e68f93;
	}
</style>