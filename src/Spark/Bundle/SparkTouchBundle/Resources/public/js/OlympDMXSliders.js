Ext.define('Spark.Sliderfield', {
    extend: 'Ext.field.Slider',

    config: {
        minValue: 0,
        maxValue: 255,

        type: 'r',
        uri: 'olymp/set'
    },

    constructor: function () {
        this.on("change", this.onChange);

        this.callParent(arguments);

    },
    onChange: function (me, sl, thumb, newValue, oldValue, eOpts) {
        Ext.Ajax.request({
            url: this.config.uri + '/'+this.config.type+'/'+newValue,

            success: function(response){
                var text = response.responseText;
                // process server response here
            }
        });
    }
});


Ext.apply(Ext.util, {
    repeat: function(taskName, fn, millis, zeroDayExecution) {
        this.tasks = this.tasks || {};
        if (zeroDayExecution)
            fn();
        return this.tasks[taskName] = window.setInterval(fn, millis);
    },

    cancelRepeatingTask: function(taskName) {
        if (this.tasks) {
            var id = this.tasks[taskName];
            if (!Ext.isEmpty(id)) {
                window.clearInterval(id);
                delete this.tasks[taskName];
            }
        }
    },

    cancelAllRepeatingTasks: function() {
        if (this.tasks)
            Object.keys(this.tasks).forEach(function(key) {
                    this.cancelRepeatingTask(key); },
                this);
    }
});

Ext.application({
    launch: function() {

        this.red = Ext.create('Spark.Sliderfield', { type: 'r'});
        this.green = Ext.create('Spark.Sliderfield', { type: 'g'});
        this.blue = Ext.create('Spark.Sliderfield', { type: 'b'});

        Ext.create('Ext.form.Panel', {
            fullscreen: true,
            items: [
                this.red,
                this.green,
                this.blue
            ]
        });

        Ext.Ajax.request({
            url: 'olymp/get',
            scope: this,
            success: function(response) {
                var text = response.responseText;
                var responseObj = Ext.decode(text);

                this.red.setValue(responseObj.data.red);
                this.green.setValue(responseObj.data.green);
                this.blue.setValue(responseObj.data.blue);
            }
        });
        
        Ext.util.repeat('task1', function () {
                Ext.Ajax.request({
                    url: 'olymp/get',
                    scope: this,
                    success: function(response) {
                        var text = response.responseText;
                        var responseObj = Ext.decode(text);

                        this.red.setValue(responseObj.data.red);
                        this.green.setValue(responseObj.data.green);
                        this.blue.setValue(responseObj.data.blue);
                    }
                });
        }.bind(this),
        10000

        );
    }
});
