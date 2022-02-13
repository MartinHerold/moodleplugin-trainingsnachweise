

(function(t) {

    console.log("startup js3");


    /**
     *
     *
     * @param id
     */
    t.save = async function(id) {
        console.log("attachments", t.attachments, id, component);
        const attachmentsResult = await t.CoreFileUploaderProvider.uploadOrReuploadFiles(
            t.attachments,
            'mod_hittrainingsnachweis',
            id,
        );
        console.log("test3", attachmentsResult);
    };

    t.onFileChange = function(){
        console.log('file changed');
    }




})(this);