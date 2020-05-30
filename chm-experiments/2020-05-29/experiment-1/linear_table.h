#include <stdio.h>
#include <stdlib.h>
#include <string.h>

/*
 * 滤波器结点
 */
typedef struct _filter_node {
    /* 结点数据, 这里以动态数组实现, 支持任意多个数据 */
    double *data;
    int len;
    int cap;
    struct _filter_node *prev; 
    struct _filter_node *next;
} filter_node;

filter_node* create_node();
void free_node(filter_node*);
void append_data(filter_node*, double);
void expand_node_cap(filter_node*);

/*
 * 线性表
 */
typedef struct _linear_table {
    filter_node *head;
    filter_node *tail;
} linear_table;

int table_length(linear_table *t) {
    int result = 0;
    filter_node *n = t->head;
    while (n) {
        result++;
        n = n->next;
    }
    return result;
}

int is_table_empty(linear_table *t) {
    return table_length(t) == 0;
}

/*
 * 将结点插入到线性表尾部
 */
void append_node_into_table(linear_table *t, filter_node *node) {
    if (is_table_empty(t)) {
        t->head = t->tail = node;
        return;
    }

    node->prev = t->tail;
    t->tail->next = node;
    t->tail = node;
}

/*
 * 将结点插入到线性表中,after_node之后的位置
 */
void insert_node_after(linear_table *t, filter_node *n, filter_node *after_node) {
    n->next = after_node->next;
    if (after_node->next) {
        after_node->next->prev = n;
    }
    n->prev = after_node;
    after_node->next = n;

    if (t->tail == after_node) {
        t->tail = n;
    }
}

/*
 * 将结点插入到线性表中,before_node之前的位置
 */
void insert_node_before(linear_table *t, filter_node *n, filter_node *before_node) {
    n->prev = before_node->prev;
    if (before_node->prev) {
        before_node->prev->next = n;
    }
    n->next = before_node;
    before_node->prev = n;

    if (t->head == before_node) {
        t->head = n;
    }
}

/*
 * 将结点从线性表中移除
 */
void remove_node(linear_table *t, filter_node *node) {
    if (node->next) {
        node->next->prev = node->prev;
    }
    if (node->prev) {
        node->prev->next = node->next;
    }
    if (t->head == node) {
        t->head = node->next;
    }
    if (t->tail == node) {
        t->tail = node->prev;
    }
}

/*
 * 创建结点
 */
filter_node* create_node(int len, int cap) {
    filter_node *n = (filter_node*)malloc(sizeof(filter_node));
    n->data = 0;
    n->len = len;
    n->cap = cap;
    n->prev = 0;
    n->next = 0;
    if (cap > 0) {
        n->data = calloc(cap, sizeof(double));
    }

    return n;
}

/*
 * 释放结点空间
 */
void free_node(filter_node *n) {
    if (n == 0) {
        return;
    }

    if (n->data != 0) {
        free(n->data);
        n->data = 0;
        n->len = 0;
        n->cap = 0;
    }
    free(n);
}

/*
 * 往结点中增加数据
 */
void append_data(filter_node *node, double data) {
    if (node->len >= node->cap) {
        expand_node_cap(node);
    }

    node->data[node->len] = data;
    node->len++;
}

/*
 * 扩展结点中的数据存储空间(动态数组的核心实现)
 */
void expand_node_cap(filter_node *node) {
    int ori_cap = node->cap;
    int new_cap = ori_cap == 0 ? 1 : ori_cap * 2;
    double *ori_data = node->data;
    double *new_data = (double*)calloc(new_cap, sizeof(double));
    int iter;
    if (ori_data != 0) {
        memcpy(new_data, ori_data, sizeof(double) * node->len);
    }

    node->cap = new_cap;
    node->data = new_data;
    free(ori_data);
}