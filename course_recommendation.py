import collections
import csv
import os
import argparse
import itertools

# this is equivalent to the following:
# class Grade:
#    def __init__( self, student, course, grade ):
#       self.student = student
#       self.course = course
#       self.grade = grade
Grade = collections.namedtuple( 'Grade', [ 'student', 'course', 'grade', 'description' ] )

class Dataset(collections.namedtuple('Dataset', ['students', 'courses', 'grades'])):
   __slots__ = ()
   def __str__(self):
      out = 'Students: {:,d}\n'.format(self.n_students)
      out += 'Courses: {:,d}\n'.format(self.n_courses)
      out += 'Grades: {:,d}\n'.format(self.n_grades)
      return out

   @property
   def n_students(self):
      return len(self.students)

   @property
   def n_courses(self):
      return len(self.courses)

   @property
   def n_grades(self):
      return len(self.grades)

   def student_grades(self, student):
      return list(r for r in self.grades if r.student == student)

   def course_grades(self, course):
      return list(r for r in self.grades if r.student == student)

   def filter_grades(self, students, courses):
      return list(((r.student, r.course), r.grade)
                   for r in self.grades
                   if r.student in students
                   and r.course in courses)

def new_dataset(grades):
   students = set(r.student for r in grades)
   courses = set(r.course for r in grades)
   return Dataset(students, courses, grades)

def gradeToNumeric( grade ):
   numeric = 0.0
   if grade == "A H" or grade == "A":
      numeric = 100.0
   elif grade.startswith( "A-" ):
      numeric = 90.0
   elif grade.startswith( "B+" ):
      numeric = 85.0
   elif grade == "B H" or grade == "B":
      numeric = 80.0
   elif grade.startswith( "B-" ):
      numeric = 75.0
   elif grade == "C+":
      numeric = 70.0
   elif grade == "C":
      numeric = 65.0
   elif grade == "C-":
      numeric = 60.0
   elif grade == "D+":
      numeric = 55.0
   elif grade == "D" or grade == "D-":
      numeric = 50.0
   elif grade == "E" or grade == "E-N":
      numeric = 40.0
   return numeric

ValidGrades = ["A H","A","A-","A-H","B H","B","B+","B+H","B-","B-H","C","C+","C-","D","D+","D-","E","E-N"]

def load_institutional_data_grades( path ):
   grades_csv = path
   if not os.path.isfile(grades_csv):
      raise Exception('File not found: \'{}\''.format(grades_csv))
   grades = list()
   with open(grades_csv, newline='') as f:
      reader = csv.reader(f, delimiter="@")
      next(reader) # skip header
      for data_tuple in reader:
         student = data_tuple[ 2 ]
         coursePrefix = data_tuple[ 6 ]
         courseNumber = data_tuple[ 10 ]
         course = coursePrefix + courseNumber
         description = data_tuple[ 11 ]
         grade = data_tuple[ 16 ]
         if grade not in ValidGrades:
            continue
         numericGrade = gradeToNumeric( grade )
         grades.append(Grade(student,
                             course,
                             numericGrade,
                             description) )
   return grades

def load_institutional_data( path ):
   grades = load_institutional_data_grades( path )
   dataset = new_dataset(grades)
   return dataset

def split_dataset(dataset, train_ratio=0.80):
    grades = dataset.grades
    size = int(len(grades) * train_ratio)
    train_grades = grades[:size]
    test_grades = grades[size:]
    return new_dataset(train_grades), new_dataset(test_grades)

from enum import Enum

class Cluster(Enum):
   HEAVY = 'heavy'
   MODERATE = 'moderate'
   LIGHT = 'light'
   ACCIDENTAL = 'accidental'

   def __str__(self):
      return self.value

# Map Student <-> index
# Map Course <-> index
IndexMapping = collections.namedtuple('IndexMapping', ['students_to_idx',
                                                       'students_from_idx',
                                                       'courses_to_idx',
                                                       'courses_from_idx'])

def map_index(values):
   values_from_idx = dict(enumerate(values))
   values_to_idx = dict((value, idx) for idx, value in values_from_idx.items())
   return values_to_idx, values_from_idx

def new_mapping(dataset):
   students_to_idx, students_from_idx = map_index(dataset.students)
   courses_to_idx, courses_from_idx = map_index(dataset.courses)
   return IndexMapping(students_to_idx, students_from_idx, courses_to_idx, courses_from_idx)

import tensorflow as tf
import numpy as np

from tensorflow.contrib.factorization import WALSModel

class ALSRecommenderModel:
    def __init__(self, user_factors, item_factors, mapping):
        self.user_factors = user_factors
        self.item_factors = item_factors
        self.mapping = mapping

    def transform(self, x):
        for student, course in x:
            if student not in self.mapping.students_to_idx \
                or course not in self.mapping.courses_to_idx:
                yield (student, course), 0.0
                continue
            i = self.mapping.students_to_idx[student]
            j = self.mapping.courses_to_idx[course]
            u = self.user_factors[i]
            v = self.item_factors[j]
            r = np.dot(u, v)
            yield (student, course), r

    def recommend(self, student, num_courses=10, courses_exclude=set()):
        i = self.mapping.students_to_idx[student]
        u = self.user_factors[i]
        V = self.item_factors
        P = np.dot(V, u)
        rank = sorted(enumerate(P), key=lambda p: p[1], reverse=True)

        top = list()
        k = 0
        while k < len(rank) and len(top) < num_courses:
            j, r = rank[k]
            k += 1

            course = self.mapping.courses_from_idx[j]
            if course in courses_exclude:
                continue

            top.append((course, r))

        return top

class ALSRecommender:

    def __init__(self, num_factors=10, num_iters=10, reg=1e-1):
        self.num_factors = num_factors
        self.num_iters = num_iters
        self.regularization = reg

    def fit(self, dataset, verbose=False):
        with tf.Graph().as_default(), tf.Session() as sess:
            input_matrix, mapping = self.sparse_input(dataset)
            model = self.als_model(dataset)
            self.train(model, input_matrix, verbose)
            row_factor = model.row_factors[0].eval()
            col_factor = model.col_factors[0].eval()
            return ALSRecommenderModel(row_factor, col_factor, mapping)

    def sparse_input(self, dataset):
        mapping = new_mapping(dataset)

        indices = [(mapping.students_to_idx[r.student],
                    mapping.courses_to_idx[r.course])
                   for r in dataset.grades]
        values = [r.grade for r in dataset.grades]
        shape = (dataset.n_students, dataset.n_courses)

        return tf.SparseTensor(indices, values, shape), mapping

    def als_model(self, dataset):
        return WALSModel(
            dataset.n_students,
            dataset.n_courses,
            self.num_factors,
            regularization=self.regularization,
            unobserved_weight=0)

    def train(self, model, input_matrix, verbose=False):
        rmse_op = self.rmse_op(model, input_matrix) if verbose else None

        row_update_op = model.update_row_factors(sp_input=input_matrix)[1]
        col_update_op = model.update_col_factors(sp_input=input_matrix)[1]

        model.initialize_op.run()
        model.worker_init.run()
        for _ in range(self.num_iters):
            # Update students
            model.row_update_prep_gramian_op.run()
            model.initialize_row_update_op.run()
            row_update_op.run()
            # Update courses
            model.col_update_prep_gramian_op.run()
            model.initialize_col_update_op.run()
            col_update_op.run()

            if verbose:
                print('RMSE: {:,.3f}'.format(rmse_op.eval()))

    def approx_sparse(self, model, indices, shape):
        row_factors = tf.nn.embedding_lookup(
            model.row_factors,
            tf.range(model._input_rows),
            partition_strategy="div")
        col_factors = tf.nn.embedding_lookup(
            model.col_factors,
            tf.range(model._input_cols),
            partition_strategy="div")

        row_indices, col_indices = tf.split(indices,
                                            axis=1,
                                            num_or_size_splits=2)
        gathered_row_factors = tf.gather(row_factors, row_indices)
        gathered_col_factors = tf.gather(col_factors, col_indices)
        approx_vals = tf.squeeze(tf.matmul(gathered_row_factors,
                                           gathered_col_factors,
                                           adjoint_b=True))

        return tf.SparseTensor(indices=indices,
                               values=approx_vals,
                               dense_shape=shape)

    def rmse_op(self, model, input_matrix):
       approx_matrix = self.approx_sparse(model, input_matrix.indices, input_matrix.dense_shape)
       err = tf.sparse_add(input_matrix, approx_matrix * (-1))
       err2 = tf.square(err)
       n = input_matrix.values.shape[0].value
       return tf.sqrt(tf.sparse_reduce_sum(err2) / n)

def main( path ):
   small_dataset = load_institutional_data( path )
   train_valid_data, test_data = split_dataset(small_dataset)
   train_data, valid_data = split_dataset(train_valid_data)
   print('Train\n\n{}'.format(train_data))
   print('Validation\n\n{}'.format(valid_data))
   print('Test\n\n{}'.format(test_data))

   train_eval = list(((r.student, r.course), r.grade) for r in train_data.grades)
   print('Evaluation grades for train: {:,d}'.format(len(train_eval)))

   # only courses in train will be available for validation
   valid_courses = train_data.courses & valid_data.courses
   print('Courses in train and validation: {:,d}'.format(len(valid_courses)))

   # students from validation that have any courses from train
   valid_students = set(r.student for r in valid_data.grades if r.course in train_data.courses)
   print('Students in validation with train courses: {:,d}'.format(len(valid_students)))

   # only students in train are available for validation
   valid_students &= train_data.students
   print('Students in train and validation: {:,d}'.format(len(valid_students)))

   valid_eval = valid_data.filter_grades(valid_students, valid_courses)
   print('Evaluation grades for validation: {:,d}'.format(len(valid_eval)))

   valid_student_clusters = collections.defaultdict(list)

   for student in valid_students:
      n_train = 0
      for r in train_data.student_grades(student):
         if r.course in valid_courses:
            n_train += 1
      n_valid = 0
      for r in valid_data.student_grades(student):
         if r.course in valid_courses:
            n_valid += 1
      cluster = None
      if n_train < 5 or n_valid < 5 or n_train + n_valid < 5:
         cluster = Cluster.ACCIDENTAL
      elif n_train + n_valid > 32:
         cluster = Cluster.HEAVY
      elif n_train + n_valid > 24:
         cluster = Cluster.MODERATE
      else:
         cluster = Cluster.LIGHT

      valid_student_clusters[cluster].append(student)
      #print('student={}, (train, valid) = ({}, {}), {}'.format(student, n_train, n_valid, cluster))

   for cluster in Cluster:
      students = valid_student_clusters[cluster]
      print(cluster, len(students))

   valid_clusters = dict()

   for cluster in Cluster:
      students = valid_student_clusters[cluster]
      if not students:
         continue
      eval_data = valid_data.filter_grades(students, valid_courses)
      if not eval_data:
         continue
      valid_clusters[cluster] = eval_data
      print('Evaluation grades for {}: {:,d}'.format(cluster, len(eval_data)))

   # only courses in train will be available for test
   test_courses = train_data.courses & test_data.courses
   print('Courses in train and test: {:,d}'.format(len(test_courses)))

   # students from test that has any course from train
   test_students = set(r.student for r in test_data.grades if r.course in train_data.courses)
   print('Students in test with train courses: {:,d}'.format(len(test_students)))

   # only students in train are available for test
   test_students &= train_data.students
   print('Students in train and test: {:,d}'.format(len(test_students)))

   test_eval = test_data.filter_grades(test_students, test_courses)
   print('Evaluation grades for test: {:,d}'.format(len(test_eval)))

   als = ALSRecommender()
   als_model = als.fit(train_data, verbose=True)

   for k in range(10):
       x, y  = valid_eval[k]
       _,  y_hat = list(als_model.transform([x]))[0]
       print(*x, y, y_hat)

   def _rmse(model, data):
       x, y = zip(*data)
       y_hat = list(r_hat for _, r_hat in model.transform(x))
       return np.sqrt(np.mean(np.square(np.subtract(y, y_hat))))

   def eval_rmse(model):
       rmse = _rmse(model, train_eval)
       print('RMSE (train): {:,.3f}'.format(rmse))

       rmse = _rmse(model, valid_eval)
       print('RMSE (validation): {:,.3f}'.format(rmse))

       for cluster in Cluster:
           eval_data = valid_clusters.get(cluster, None)
           if not eval_data:
               continue
           rmse = _rmse(model, eval_data)
           print('RMSE for {}: {:,.3f}'.format(cluster, rmse))

   eval_rmse(als_model)

   num_factors = [ 100, 200 ]
   num_iters=[5]
   factorIters = list(itertools.product(num_factors, num_iters))
   for ( num_factor, num_iter ) in factorIters:
      print('num_factor {} num_iter {}'.format( num_factor, num_iter ) )
      als = ALSRecommender(num_factors=num_factor, num_iters=num_iter, reg=0.1)
      print('Training...\n')
      als_model = als.fit(train_data, verbose=True)
      print('\nEvaluation...\n')
      eval_rmse(als_model)

      # valid_clusters: dict[cluster: Cluster, list[((student: str, course: str), grade: float)]]
      # 0 -> first user-item-pair-and-grade, 0 -> user-item-pair, 0 -> user
      student = valid_clusters[Cluster.LIGHT][0][0][0] 

      user_courses = sorted([(r.course, r.grade)
                           for r in valid_data.grades
                           if r.student == student \
                               and r.course in train_data.courses],
                          key=lambda r: r[1],
                          reverse=True)

      courses_exclude = set(r.course for r in train_data.grades if r.student == student)

      rec_courses = als_model.recommend(student, courses_exclude=courses_exclude)

      user_top = dict()
      p_grade = None
      p = 0
      print('Top courses for {}:\n'.format(student))
      for i, (course, grade) in enumerate(user_courses):
          if p_grade is None or p_grade > grade:
              p_grade = grade
              p += 1
          user_top[course] = p
          if i < 20:
              print('[{}] {}, {:,.2f}'.format(p, course, grade))
      print()

      p_grade = None
      p = 0
      print('Recommendations for {}:\n'.format(student))
      for course, grade in rec_courses:
          if p_grade is None or p_grade > grade:
              p_grade = grade
              p += 1
          print('[{}] {}, {:,.2f}, {}'.format(p, course, grade, user_top.get(course, '-')))

if __name__ == "__main__":
   parser = argparse.ArgumentParser(description='Recommend courses based on grades' )
   parser.add_argument('path', help="Input file path" )
   args = parser.parse_args()
   main( args.path )
