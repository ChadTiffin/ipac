<template>
	<div class="form-group">
		<label class="control-label" :class="colClass">{{ label }}</label>

		<div :class="fieldClass">
			<slot></slot>
		</div>
	</div>
</template>

<script type="text/javascript">

	export default {
		name: "FormGroup",
		props: ["label","colClass"],
		methods: {
			updateValue(value) {
				this.$emit("input",value)
			}
		},
		computed: {
			fieldClass () {

				//split into classes

				let classes = this.colClass.split(" ");

				let returnedClasses = ""
				classes.forEach(function(singleClass, index) {

					//Compute the field column size from the label column size
					let num_cols_arry = singleClass.split("-")
					let num_cols = parseInt(num_cols_arry[2])

					let difference_cols = 12 - num_cols

					returnedClasses += " col-" + num_cols_arry[1] + "-"+difference_cols

				})

				return returnedClasses
				
			}
		}
	}
</script>