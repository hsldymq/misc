%%%%%产生带Key值的数据文件
clc
clear all
I=imread('F:\数据结构课件\数据结构实训\实验1\stand.jpg');
I=imresize(I,0.1);
m=size(I,1);
n=size(I,2);
A=zeros(5,m*n);
A(1,:)=1:m*n;
R=I(:,:,1);
G=I(:,:,2);
B=I(:,:,3);
A(2,:)=[m*n:-1:1];
A(3,:)=R(:)';
A(4,:)=G(:)';
A(5,:)=B(:)';
fileID = fopen('F:\数据结构课件\数据结构实训\实验1\db.txt','w');

fprintf(fileID,'%i,%f,%f,%f,%f\n',A);
fclose(fileID);
%return;
%%%%%产生不带Key值的数据文件

I=imread('F:\数据结构课件\数据结构实训\实验1\stand.jpg');
I=imresize(I,0.1);
m=size(I,1);
n=size(I,2);
A=zeros(4,m*n);
A(1,:)=1:m*n;
R=I(:,:,1);
G=I(:,:,2);
B=I(:,:,3);
A(2,:)=R(:)';
A(3,:)=G(:)';
A(4,:)=B(:)';
fileID = fopen('F:\数据结构课件\数据结构实训\实验1\dbexp.txt','w');

fprintf(fileID,'%i,%i,%i,%i\n',A);
fclose(fileID);
