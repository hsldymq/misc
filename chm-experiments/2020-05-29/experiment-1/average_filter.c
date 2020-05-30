#include <stdio.h>
#include "linear_table.h"

/*
 * 题目一, 第二分题: 均值滤波
 * 
 * 参数1: 输入数据文件的绝对路径
 * 参数2: 输出数据文件的绝对路径
 */
int main(int args, char *argv[]) {
    FILE *input_file;
    FILE *output_file;
    char c, input_line[255];
    int iter;
    double number = 0;
    int negative = 0, start_input = 0;
    linear_table table = {};
    linear_table target_tabel = {};
    filter_node *current_node, *iter_node, *node;

    if (args < 3) {
        printf("请提供数据的输入文件路径和输出文件路径\n");
        return -1;
    }

    input_file = fopen(argv[1], "r");
    if (!input_file) {
        printf("打开输入文件失败\n");
        return -1;
    }

    output_file = fopen(argv[2], "w+");
    if (!output_file) {
        printf("打开输出文件失败\n");
        return -1;
    }

    while (fgets(input_line, 255, input_file)) {
        for (iter = 0; iter < 254; iter++) {
            c = input_line[iter];
            if (c >= '0' && c <= '9') {
                if (!current_node) {
                    current_node = create_node(0, 0);
                }
                number = number * 10 + (c - '0');
                start_input = 1;
            } else if (c == ',') {
                if (!current_node) {
                    printf("逗号前没有数字");
                    return -1;
                }
                if (negative) {
                    number = -number;
                }
                append_data(current_node, number);
                negative = 0;
                start_input = 0;
                number = 0;
            } else if (c == '-') {
                if (start_input) {
                    printf("不能在数字后面出现负号");
                    return -1;
                }
                negative = 1;
                start_input = 1;
            } else if (c == '\n') {
                if (negative) {
                    number = -number;
                }
                if (current_node) {
                    if (start_input) {
                        append_data(current_node, number);
                    }
                    append_node_into_table(&table, current_node);
                    current_node = 0;
                }
                start_input = 0;
                negative = 0;
                number = 0;
                break;
            } else if (c == ' ') {
                continue;
            } else if (c == '\0') {
                if (current_node && start_input) {
                    append_data(current_node, number);
                    append_node_into_table(&table, current_node);
                }
                break;
            } else {
                printf("错误输入");
                return -1;
            }
        }
    }

    iter_node = table.head;
    while (iter_node) {
        node = create_node(iter_node->len, iter_node->cap);
        if (node->cap > 0) {
            memcpy(node->data, iter_node->data, node->cap * sizeof(double));
        }
        append_node_into_table(&target_tabel, node);
        if (!iter_node->prev || !iter_node->next) {
            iter_node = iter_node->next;
            continue;
        }

        if (iter_node->len != iter_node->prev->len || iter_node->len != iter_node->next->len) {
            printf("长度不一致");
            return -1;
        }

        for (iter = 0; iter < iter_node->len; iter++) {
            node->data[iter] = (iter_node->data[iter] + iter_node->prev->data[iter] + iter_node->next->data[iter]) / 3;
        }

        iter_node = iter_node->next;
    }

    iter_node = target_tabel.head;
    while (iter_node) {
        for (iter = 0; iter < iter_node->len; iter++) {
            sprintf(input_line, "%f", iter_node->data[iter]);
            fputs(input_line, output_file);
            if (iter != iter_node->len - 1) {
                fputc(',', output_file);
            }
        }
        fputc('\n', output_file);
        iter_node = iter_node->next;
    }

    return 0;
}