<?php
//定义一个函数，用于返回JSON格式的响应
function response($success, $error) {
    //创建一个数组，包含成功标志和错误信息
    $response = array(
        "success" => $success,
        "error" => $error
    );
    //将数组转换为JSON字符串，并输出
    echo json_encode($response);
}
//检查请求是否是POST方法
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    //检查请求是否包含图片文件和作者名字
    if (isset($_FILES["image"]) && isset($_POST["author"])) {
        //获取图片文件和作者名字
        $image = $_FILES["image"];
        $author = $_POST["author"];
        //获取图片文件的名称和大小
        $imageName = $image["name"]; //例如 "cat.jpg"
        $imageSize = $image["size"]; //例如 123456
        //检查图片文件是否符合格式和大小限制
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION); //例如 "jpg"
        $allowedExtensions = array("jpg", "png", "gif"); //允许的图片格式
        $maxSize = 1024 * 1024; //允许的最大图片大小，单位是字节
        if (!in_array($imageExtension, $allowedExtensions)) {
            //如果图片格式不支持，返回错误信息
            response(false, "图片格式不支持，请上传jpg, png或gif格式的图片");
            exit(); //终止脚本
        }
        if ($imageSize > $maxSize) {
            //如果图片大小超过限制，返回错误信息
            response(false, "图片大小超过限制，请上传不超过1MB的图片");
            exit(); //终止脚本
        }
        //定义一个目录，用于存储上传的图片文件
        $uploadDir = "../image/imgs_bqb/";
        //定义一个文件名，用于重命名上传的图片文件，避免重复或乱码，这里使用当前时间戳加上原始扩展名
        $uploadName = time() . "." . $imageExtension; //例如 "1637823456.jpg"
        //定义一个完整的路径，用于移动上传的图片文件到目标目录
        $uploadPath = $uploadDir . $uploadName; //例如 "main/image/imgs_bqb/1637823456.jpg"
        //尝试移动上传的图片文件到目标目录
        if (move_uploaded_file($image["tmp_name"], $uploadPath)) {
            //如果移动成功，表示上传成功，继续处理后续逻辑
            //定义一个JSON文件的路径，用于存储图片信息
            $jsonPath = $uploadDir . "name.json"; //例如 "main/image/imgs_bqb/name.json"
            //检查JSON文件是否存在，如果不存在，创建一个空的数组，否则，读取JSON文件的内容，并转换为数组
            if (!file_exists($jsonPath)) {
                //创建一个空的数组
                $jsonArray = array();
            } else {
                //读取JSON文件的内容，并转换为数组
                $jsonContent = file_get_contents($jsonPath);
                $jsonArray = json_decode($jsonContent, true);
            }
            //创建一个新的数组，用于存储当前上传的图片信息，包括URL和作者名字
            $newArray = array(
                "url" => $uploadPath,
                "up" => $author
            );
            //将新的数组添加到原来的数组中
            array_push($jsonArray, $newArray);
            //将更新后的数组转换为JSON字符串，并写入到JSON文件中
            $newContent = json_encode($jsonArray);
            file_put_contents($jsonPath, $newContent);
            //返回成功信息
            response(true, "");
        } else {
            //如果移动失败，表示上传失败，返回错误信息
            response(false, "移动文件失败");
        }
    } else {
        //如果请求不包含图片文件和作者名字，返回错误信息
        response(false, "缺少图片文件或作者名字");
    }
} else {
    //如果请求不是POST方法，返回错误信息
    response(false, "请求方法不正确");
}
?>
