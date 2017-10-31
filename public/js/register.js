(function() {

    // 学科が変更されたらaction
    $('select[name="major"]').change(function() {

        // 選択した学科のクラス名取得
        var majorName = $('select[name="major"] option:selected').attr("class");
        console.log(majorName);

        // コース名の数を取得
        var count = $('select[name="course"]').children().length;

        // コース名の数分ループ
        for (var i=0; i<count; i++) {

            var course = $('select[name="course"] option:eq(' + i + ')');

            //コース名のクラスと学科クラスが一致しているか
            if(course.attr("class") === majorName) {
                course.show();
            }else {
                    course.hide();
                }
            }
    })
});