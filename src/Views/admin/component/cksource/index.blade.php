<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
    <title>LARAVELPHP - File Browser</title>
</head>
<body>

<script src="{{@assets('assets/ckfinder/ckfinder.js')}}"></script>
<script>
    CKFinder.start({
        plugins: [
            '{{@assets("assets/ckfinder/plugins/StatusBarInfo/StatusBarInfo.js")}}'
        ],
        onInit: function(finder) {
            function showErrorDialog(errorMessage) {
                finder.request( 'dialog:info', { msg: errorMessage } );
            }
            finder.request('files:deleted',function (event){
                console.log(event);
            });
            finder.on('command:error:FileUpload', function(event) {
                console.log(event.data.response);
                const response = event.data.response;
                showErrorDialog(response.error.message);

            });
        }
    });
</script>
</body>
</html>