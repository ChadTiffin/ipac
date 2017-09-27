<template>

	<div :class="{'btn-group-vertical' : vertical, 'btn-group': !vertical, 'full-width' : fullWidth}">
		<button 
			v-for="(button,index) in buttons" 
			type="button" 
			class="btn btn-default" 
			:class="{active: activeIndex==index, 'full-width': fullWidth}" 
			v-on:click="input(button.value, index)">
			<i v-if="'icon' in button" class="fa" :class="button.icon"></i>
			{{button.text}}
		</button>
	</div>

</template>

<script type="text/javascript">

	export default {
		name: "ButtonGroup",
		props: ["value","buttons","vertical","fullWidth"],
		data() {
			return {
				activeIndex: null
			}
		},
		methods: {
			input(value, index) {
				this.activeIndex = index

				this.$emit("input",value)
				this.$emit("change",value)
			}
		},
		mounted() {
			//loop through buttons to match prop value to button value
			let vm = this
			this.buttons.forEach(function(button, index){
				if (button.value == vm.value)
					vm.activeIndex = index
			})
		}
	}
</script>

<style type="text/css" scoped>
	.btn-group .active, .btn-group-vertical .active {
		background-color: #428bca;
		color: white;
		text-shadow: none;
	}

	div.full-width {
		display: block;
	}

	button.full-width {
		width: 100%;
		display: block;
	}
</style>