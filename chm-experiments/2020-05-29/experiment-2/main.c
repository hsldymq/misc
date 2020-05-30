#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef struct _student {
    char *id;
    char *name;
    char *gender;
    char *class;
} student;

typedef struct _node {
    student *stu;
    int weight;
    struct _node *left_child;
    struct _node *right_child;
} node;

node* create_node(student *stu) {
    node *n = (node*)malloc(sizeof(node));
    int weight;
    n->stu = stu;
    if (stu) {
        n->weight = atoi(stu->id + (strlen(stu->id) - 3));
    }
    
    return n;
}

typedef struct _tree {
    node *root;
} tree;

tree *create_tree(node *root) {
    tree *t = (tree*)malloc(sizeof(tree));
    t->root = root;
    return t;
}

void free_tree(tree *t) {
    if (t != 0) {
        free(t);
    }
}

/*
 * 完成实验二, 题目二
 * 从文件中读取学生数据,按照权重值生成哈夫曼树,最终输出哈夫曼树权重值
 * 程序接受一个参数: 数据输入文件的路径.
 */
int main(int args, char *argv[]) {
    FILE *input_file;
    char c, input_line[255];
    int iter = 0, negative = 0, start_input = 0;
    int field_index = 0, info_start_index = 0, field_value_length = 0;
    char *field_value;
    student *current_student;
    node *current_node;
    tree *current_tree;
    tree *forest[255];
    int forest_length = 0;
    int lowest_trees_indexes[2] = {-1 , -1};
    int i;

    for (iter = 0; iter < 255; iter++) {
        forest[iter] = 0;
    }

    if (args < 2) {
        printf("请提供数据的输入文件路径\n");
        return -1;
    }

    input_file = fopen(argv[1], "r");
    if (!input_file) {
        printf("打开输入文件失败\n");
        return -1;
    }

    while (fgets(input_line, 255, input_file)) {
        info_start_index = 0;
        for (iter = 0; iter < 254; iter++) {
            c = input_line[iter];
            if (c == ',') {
                if (!current_student) {
                    current_student = (student*)malloc(sizeof(student));
                }

                field_value_length = iter - info_start_index + 1;
                field_value = (char*)malloc(field_value_length);
                field_value[field_value_length - 1] = '\0';
                memcpy(field_value, input_line + info_start_index, field_value_length - 1);
                if (field_index == 0) {
                    current_student->id = field_value;
                } else if (field_index == 1) {
                    current_student->name = field_value;
                } else if (field_index == 2) {
                    current_student->gender = field_value;
                } else if (field_index == 3) {
                    current_student->class =  field_value;
                }
                

                field_index++;
                info_start_index = iter + 1;
            } else if (c == '\n' || c == '\0') {
                if (current_student) {
                    if (field_index == 3) {
                        field_value_length = iter - info_start_index + 1;
                        field_value = (char*)malloc(field_value_length);
                        field_value[field_value_length - 1] = '\0';
                        memcpy(field_value, input_line + info_start_index, field_value_length - 1);
                        current_student->class =  field_value;
                    }

                    current_node = create_node(current_student);
                    forest[forest_length++] = create_tree(current_node);
                    printf("读入学生数据 - 权值:%d, 学号:%s, 姓名:%s, 性别:%s, 班级:%s\n", current_node->weight, current_node->stu->id, current_node->stu->name, current_node->stu->gender, current_node->stu->class);
                    current_student = 0;
                }
                
                field_index = 0;                    
                break;
            }
        }
    }

    while (forest_length > 1) {
        lowest_trees_indexes[0] = -1;
        lowest_trees_indexes[1] = -1;
        for (iter = 0; iter < 255; iter++) {
            if (!forest[iter]) {
                continue;
            }
            
            i = lowest_trees_indexes[0];
            if (i < 0) {
                lowest_trees_indexes[0] = iter;
                continue;
            } else if (forest[i]->root->weight >= forest[iter]->root->weight && i != iter) {
                lowest_trees_indexes[1] = lowest_trees_indexes[0];
                lowest_trees_indexes[0] = iter;
                continue;
            } 
            
            i = lowest_trees_indexes[1];
            if (i < 0) {
                lowest_trees_indexes[1] = iter;
            } else if (forest[i]->root->weight >= forest[iter]->root->weight && i != iter) {
                lowest_trees_indexes[1] = iter;
            }
        }

            
        current_node = create_node(0);
        current_node->left_child = forest[lowest_trees_indexes[0]]->root;
        current_node->right_child = forest[lowest_trees_indexes[1]]->root;
        current_node->weight = current_node->left_child->weight + current_node->right_child->weight;
        current_tree = create_tree(current_node);
        free_tree(forest[lowest_trees_indexes[0]]);
        free_tree(forest[lowest_trees_indexes[1]]);
        forest[lowest_trees_indexes[0]] = current_tree;
        forest[lowest_trees_indexes[1]] = 0;
        forest_length--;
    }
    
    printf("哈夫曼树权值为: %d\n", current_tree->root->weight);

    return 0;
}
