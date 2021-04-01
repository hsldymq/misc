package main

import (
	"bufio"
	"bytes"
	"fmt"
	"io/ioutil"
	"os"
	"strings"

	"github.com/ledongthuc/pdf"
	"github.com/nguyenthenguyen/docx"
)

var keywords []string

func init() {
	fmt.Printf("输入关键词,多个关键词以空格分割: ")

	inputReader := bufio.NewReader(os.Stdin)
	kw, err := inputReader.ReadString('\n')
	if err != nil {
		fmt.Printf("The input was: %s\n", kw)
	}

	kwArr := strings.Split(kw, " ")
	for _, each := range kwArr {
		k := strings.TrimSpace(each)
		if k != "" {
			keywords = append(keywords, k)
		}
	}
}

func main() {
	workDir,err := os.Getwd()

	if err != nil {
		panic(err)
	}
	
	files, err := GetAllFiles(workDir)
	if err != nil {
		panic(files)
	}

	fmt.Println("搜索结果:")
	for _, filePath := range files {
		if strings.HasSuffix(filePath, "pdf") {
			if match, _ := searchPDF(filePath, keywords); match {
				fmt.Println("    "+filePath)
			}
		} else if strings.HasSuffix(filePath, "docx") {
			if match, _ := searchDocx(filePath, keywords); match {
				fmt.Println("    "+filePath)
			}
		} 
	}
}


func searchDocx(filePath string, keywords []string) (bool, error) {
	r, err := docx.ReadDocxFile(filePath)
	if err != nil {
		return false, err
	}

	content := r.Editable().GetContent()
	for _, each := range keywords {
		if strings.Contains(content, each) {
			return true, nil
		}
	}
	r.Close()

	return false, nil
}

func searchPDF(filePath string, keywords []string) (bool, error) {
	f, r, err := pdf.Open(filePath)
	// remember close file
    defer f.Close()
	if err != nil {
		return false, err
	}
	var buf bytes.Buffer
    b, err := r.GetPlainText()
    if err != nil {
        return false, err
    }
    buf.ReadFrom(b)
	
	content := buf.String()

	for _, each := range keywords {
		if strings.Contains(content, each) {
			return true, nil
		}
	}

	return false, nil
}

//获取指定目录下的所有文件,包含子目录下的文件
func GetAllFiles(dirPth string) (files []string, err error) {
    var dirs []string
    dir, err := ioutil.ReadDir(dirPth)
    if err != nil {
        return nil, err
    }

    PthSep := string(os.PathSeparator)
    //suffix = strings.ToUpper(suffix) //忽略后缀匹配的大小写

    for _, fi := range dir {
        if fi.IsDir() { // 目录, 递归遍历
			continue
            dirs = append(dirs, dirPth+PthSep+fi.Name())
            GetAllFiles(dirPth + PthSep + fi.Name())
        } else {
			files = append(files, dirPth+PthSep+fi.Name())
        }
    }

    // 读取子目录下文件
    for _, table := range dirs {
        temp, _ := GetAllFiles(table)
        for _, temp1 := range temp {
            files = append(files, temp1)
        }
    }

    return files, nil
}