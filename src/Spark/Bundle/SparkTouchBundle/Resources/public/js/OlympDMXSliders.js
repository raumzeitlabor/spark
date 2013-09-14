Ext.define('Spark.Sliderfield', {
    extend: 'Ext.field.Slider',

    minValue: 0,
    maxValue: 255,
    type: 'r',
    uri: 'olymp/set',

    constructor: function () {
        this.on("change", this.onChange);

        this.callParent(arguments);

    },
    onChange: function (me, sl, thumb, newValue, oldValue, eOpts) {
        console.log(newValue);
        Ext.Ajax.request({
            url: this.uri + '/'+this.type+'/'+newValue,

            success: function(response){
                var text = response.responseText;
                // process server response here
            }
        });
        console.log('value changed!', newValue);


    }
});


Ext.application({
    launch: function() {
        Ext.create('Ext.form.Panel', {
            fullscreen: true,
            items: [
                Ext.create('Spark.Sliderfield', { type: 'r'}),
                Ext.create('Spark.Sliderfield', { type: 'g'}),
                Ext.create('Spark.Sliderfield', { type: 'b'})
            ]
        });
    }
});