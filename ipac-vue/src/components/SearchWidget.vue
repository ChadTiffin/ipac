<template>

	<form class="pull-right search-bar" v-on:submit.prevent="submit">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="Search..." :value="value" v-on:input="input($event.target.value)">
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
			</span>
		</div>
	</form>

</template>

<script type="text/javascript">

	export default {
		name: "SearchWidget",
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
			},
			submit() {
				this.$emit("submit",this.value)
			}
		}
	}
</script>

<style type="text/css" scoped>

.search-bar {
	max-width: 300px;
}

</style>

