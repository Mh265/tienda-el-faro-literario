-- =====================================================================
-- Proyecto: Tienda en Línea de Libros - "El Faro Literario"
-- Curso: Desarrollo Full Stack - INTECAP
-- Script completo de base de datos (MySQL): estructura + datos iniciales
-- Convención de nombres: snake_case, en español, singular para PK/FK
-- =====================================================================

CREATE DATABASE IF NOT EXISTS tienda_el_faro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tienda_el_faro;

-- ---------------------------------------------------------------------
-- Tabla: usuarios
-- Clientes y administradores del sistema
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)  NOT NULL,
    apellido        VARCHAR(100)  NOT NULL,
    correo          VARCHAR(150)  NOT NULL,
    password        VARCHAR(255)  NOT NULL,
    telefono        VARCHAR(20)   NULL,
    direccion       VARCHAR(255)  NULL,
    tipo_usuario    ENUM('cliente','administrador') NOT NULL DEFAULT 'cliente',
    fecha_registro  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_correo UNIQUE (correo)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: categorias
-- Géneros literarios (Novela, Ciencia Ficción, Infantil, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id_categoria    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)  NOT NULL,
    descripcion     VARCHAR(255)  NULL,
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: productos
-- Representa los libros del catálogo.
-- descripcion_corta: para tarjetas/listados del catálogo (Jenifer - frontend)
-- descripcion_larga: para la vista de detalle del libro
-- ---------------------------------------------------------------------
CREATE TABLE productos (
    id_producto         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_categoria        INT UNSIGNED  NOT NULL,
    nombre              VARCHAR(200)  NOT NULL,   -- título del libro
    autor               VARCHAR(150)  NOT NULL,
    editorial           VARCHAR(150)  NULL,
    descripcion_corta   VARCHAR(255)  NULL,       -- resumen breve, para listados
    descripcion_larga   TEXT          NULL,       -- sinopsis completa, para el detalle
    precio              DECIMAL(10,2) NOT NULL,
    cantidad            INT UNSIGNED  NOT NULL DEFAULT 0,  -- stock disponible
    imagen              VARCHAR(255)  NULL,
    fecha_publicacion   DATE          NULL,
    estado              ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    fecha_creacion      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_productos_autor  ON productos(autor);
CREATE INDEX idx_productos_nombre ON productos(nombre);

-- ---------------------------------------------------------------------
-- Tabla: pedidos
-- ---------------------------------------------------------------------
CREATE TABLE pedidos (
    id_pedido       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT UNSIGNED  NOT NULL,
    fecha           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total           DECIMAL(10,2) NOT NULL DEFAULT 0,
    estado          ENUM('pendiente','pagado','enviado','entregado','cancelado')
                        NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_pedidos_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: detalle_pedido
-- Relación N:M entre pedidos y productos, con datos propios de la línea
-- ---------------------------------------------------------------------
CREATE TABLE detalle_pedido (
    id_detalle      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido       INT UNSIGNED  NOT NULL,
    id_producto     INT UNSIGNED  NOT NULL,
    cantidad        INT UNSIGNED  NOT NULL,
    precio          DECIMAL(10,2) NOT NULL,  -- precio unitario al momento de la compra
    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: resenas
-- Calificaciones y comentarios de los usuarios sobre los libros
-- ---------------------------------------------------------------------
CREATE TABLE resenas (
    id_resena       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT UNSIGNED  NOT NULL,
    id_producto     INT UNSIGNED  NOT NULL,
    calificacion    TINYINT UNSIGNED NOT NULL,
    comentario      TEXT          NULL,
    fecha           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resenas_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_resenas_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT chk_resenas_calificacion CHECK (calificacion BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: wishlist
-- Lista de deseos del usuario (evita duplicados con UNIQUE compuesto)
-- ---------------------------------------------------------------------
CREATE TABLE wishlist (
    id_wishlist     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT UNSIGNED  NOT NULL,
    id_producto     INT UNSIGNED  NOT NULL,
    fecha_agregado  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_producto
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT uq_wishlist_usuario_producto UNIQUE (id_usuario, id_producto)
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS INICIALES (seed)
-- =====================================================================

-- ---------------------------------------------------------------------
-- Categorías (12 géneros literarios)
-- ---------------------------------------------------------------------
INSERT INTO categorias (nombre, descripcion) VALUES
    ('Novela',              'Narrativa de ficción extensa'),
    ('Ciencia Ficción',     'Historias basadas en ciencia y tecnología especulativa'),
    ('Infantil',            'Libros dirigidos a público infantil'),
    ('Historia',            'Libros de divulgación histórica'),
    ('Autoayuda',           'Desarrollo personal y motivacional'),
    ('Fantasía',            'Mundos y criaturas imaginarias'),
    ('Terror y Misterio',   'Suspenso, terror e intriga'),
    ('Poesía',              'Obras poéticas y lírica'),
    ('Biografía',           'Historias de vida de personajes reales'),
    ('Negocios y Finanzas', 'Administración, economía y emprendimiento'),
    ('Clásicos',            'Obras clásicas de la literatura universal'),
    ('Juvenil',             'Literatura dirigida a jóvenes (Young Adult)');

-- ---------------------------------------------------------------------
-- Usuarios (1 administrador + 5 clientes)
-- Contraseñas ya hasheadas con password_hash() (bcrypt, PHP 8.3).
-- Las contraseñas en texto plano se entregan aparte del script.
-- ---------------------------------------------------------------------
INSERT INTO usuarios (nombre, apellido, correo, password, telefono, tipo_usuario) VALUES
    ('Admin',  'El Faro Literario', 'admin@elfaroliterario.com',
        '$2y$10$kFSuCuiZdurj6ePwnYgTauNUIUE3R/BtgPSJJzfSOxY9fk.5MUJIC', NULL, 'administrador'),
    ('Ana',    'Morales',    'ana.morales@correo.com',
        '$2y$10$IQcYHkz6qGQS67M3jip6reCDD8S0TES44F6VdSPFTbBJAc.cSnBVi', '5511-2233', 'cliente'),
    ('Carlos', 'Ramírez',    'carlos.ramirez@correo.com',
        '$2y$10$7v1dFa9Ny8GfjKmAgmmgyu6r/RHEMIT7Z7cnF7tkMK2GY9W99mS8G', '5522-3344', 'cliente'),
    ('Lucía',  'Fernández',  'lucia.fernandez@correo.com',
        '$2y$10$xr.Mc.VBtRl0Pp0ybJhQku3eoCztL/9B7v9F9YSmdF46jsmy5ZBJa', '5533-4455', 'cliente'),
    ('Diego',  'Pérez',      'diego.perez@correo.com',
        '$2y$10$8.5Q6MVVDLmgy3XMqAzJ4u3ijbtDhao.BmvErXsk24GYa3EO5v4ua', '5544-5566', 'cliente'),
    ('María',  'González',   'maria.gonzalez@correo.com',
        '$2y$10$HEz/ZoS4N0yGojbFfLruWekzcZXSDtcRrXk6PMhLt9X5TN76EqqFW', '5555-6677', 'cliente');

-- ---------------------------------------------------------------------
-- Productos (30 libros) con descripcion_corta y descripcion_larga
-- id_categoria: 1 Novela | 2 Ciencia Ficción | 3 Infantil | 4 Historia
--               5 Autoayuda | 6 Fantasía | 7 Terror/Misterio | 8 Poesía
--               9 Biografía | 10 Negocios | 11 Clásicos | 12 Juvenil
-- ---------------------------------------------------------------------
INSERT INTO productos (id_categoria, nombre, autor, editorial, descripcion_corta, descripcion_larga, precio, cantidad, estado) VALUES

(1, 'Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana',
 'Novela emblemática del realismo mágico latinoamericano.',
 'Cuenta la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo. Mezcla lo real y lo fantástico para retratar los ciclos de amor, guerra, soledad y destino que marcan a Latinoamérica. Es considerada una de las obras cumbre de la literatura en español.',
 180.00, 15, 'activo'),

(1, 'La casa de los espíritus', 'Isabel Allende', 'Plaza & Janés',
 'Saga familiar marcada por lo mágico y lo político.',
 'Narra tres generaciones de la familia Trueba, entrelazando el don de clarividencia de sus mujeres con la turbulenta historia política de un país no identificado, inspirado en Chile. Combina realismo mágico con una fuerte crítica social sobre el poder y la memoria familiar.',
 165.00, 12, 'activo'),

(1, 'Rayuela', 'Julio Cortázar', 'Sudamericana',
 'Novela experimental de estructura no lineal.',
 'Puede leerse en distinto orden según las instrucciones del propio autor, lo que la convierte en un experimento narrativo único. Sigue a Horacio Oliveira entre París y Buenos Aires en su búsqueda existencial. Es una obra fundamental de la vanguardia literaria latinoamericana.',
 155.00, 8, 'activo'),

(2, 'Fahrenheit 451', 'Ray Bradbury', 'Ballantine Books',
 'Distopía sobre una sociedad que quema libros.',
 'Ambientada en un futuro donde los bomberos se dedican a incinerar libros en lugar de apagar incendios, sigue a Guy Montag, un bombero que empieza a cuestionar el sistema al que sirve. Es una reflexión sobre la censura, la conformidad social y el valor del conocimiento.',
 145.50, 10, 'activo'),

(2, 'Dune', 'Frank Herbert', 'Chilton Books',
 'Épica de ciencia ficción ambientada en el planeta Arrakis.',
 'Relata la lucha de la Casa Atreides por controlar el planeta desértico Arrakis, única fuente de la valiosa especia melange. Combina política, religión y ecología en una de las sagas de ciencia ficción más influyentes de la historia.',
 210.00, 9, 'activo'),

(2, 'Un mundo feliz', 'Aldous Huxley', 'Chatto & Windus',
 'Distopía sobre control social y tecnología.',
 'Presenta una sociedad futura organizada mediante ingeniería genética, condicionamiento psicológico y consumo de una droga llamada soma, donde la felicidad se impone a costa de la libertad individual. Es una crítica anticipada al totalitarismo tecnológico.',
 140.00, 14, 'activo'),

(2, 'Fundación', 'Isaac Asimov', 'Gnome Press',
 'Primera entrega de la saga sobre el declive de un imperio galáctico.',
 'Narra cómo el matemático Hari Seldon predice la caída del Imperio Galáctico y crea la Fundación para preservar el conocimiento humano durante la era de oscuridad que seguirá. Es un pilar de la ciencia ficción centrada en la psicohistoria.',
 175.00, 11, 'activo'),

(3, 'El Principito', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock',
 'Fábula poética sobre la amistad y la infancia.',
 'Un aviador perdido en el desierto conoce a un pequeño príncipe que viene de otro planeta y le relata sus viajes por distintos mundos. A través de un lenguaje sencillo aborda temas profundos como el amor, la pérdida y la mirada adulta frente a la infantil.',
 120.00, 25, 'activo'),

(3, 'Charlie y la fábrica de chocolate', 'Roald Dahl', 'Alfred A. Knopf',
 'Aventura de un niño en una fábrica mágica de dulces.',
 'Charlie Bucket, un niño de familia humilde, gana un boleto dorado para visitar la extraordinaria fábrica de chocolates del excéntrico Willy Wonka. Es una historia llena de fantasía con una fuerte moraleja sobre la humildad y la bondad.',
 110.00, 20, 'activo'),

(3, 'Matilda', 'Roald Dahl', 'Jonathan Cape',
 'Historia de una niña con poderes especiales y amor por los libros.',
 'Matilda es una niña prodigio incomprendida por su familia, que encuentra refugio en los libros y descubre que posee poderes telequinéticos. Con ayuda de su maestra enfrenta a los adultos que abusan de su poder, en una historia sobre justicia y autodescubrimiento.',
 115.00, 18, 'activo'),

(4, 'Sapiens: de animales a dioses', 'Yuval Noah Harari', 'Debate',
 'Recorrido por la historia de la humanidad.',
 'Analiza cómo el Homo sapiens llegó a dominar el planeta a través de las revoluciones cognitiva, agrícola y científica. Combina biología, historia y economía para explicar la evolución de las sociedades humanas y sus mitos compartidos.',
 195.00, 13, 'activo'),

(4, 'Guns, Germs, and Steel', 'Jared Diamond', 'W. W. Norton',
 'Análisis de los factores que moldearon la historia mundial.',
 'Explora por qué unas civilizaciones lograron dominar a otras, atribuyendo las diferencias no a superioridad racial sino a factores geográficos, ambientales y biológicos como la disponibilidad de especies domesticables.',
 185.00, 7, 'activo'),

(4, 'El diario de Ana Frank', 'Ana Frank', 'Contact Publishing',
 'Testimonio real de una joven durante el Holocausto.',
 'Recoge las anotaciones diarias de Ana Frank mientras su familia se escondía de la persecución nazi en Ámsterdam. Es un testimonio íntimo sobre la esperanza, el miedo y la humanidad en medio de una de las mayores tragedias del siglo XX.',
 130.00, 16, 'activo'),

(5, 'El poder del ahora', 'Eckhart Tolle', 'Namaste Publishing',
 'Guía práctica hacia la iluminación espiritual.',
 'Propone técnicas para vivir plenamente en el presente y liberarse del sufrimiento generado por la mente y el pasado. Combina filosofía oriental y espiritualidad práctica orientada al bienestar emocional cotidiano.',
 130.00, 20, 'activo'),

(5, 'Los 7 hábitos de la gente altamente efectiva', 'Stephen R. Covey', 'Free Press',
 'Principios para la efectividad personal y profesional.',
 'Presenta siete hábitos organizados en torno a la independencia, la interdependencia y la mejora continua, aplicables tanto a la vida personal como profesional. Es uno de los libros de desarrollo personal más influyentes del último medio siglo.',
 150.00, 17, 'activo'),

(5, 'Hábitos atómicos', 'James Clear', 'Avery',
 'Estrategias para construir buenos hábitos y romper malos.',
 'Explica cómo pequeños cambios de comportamiento, aplicados de forma constante, generan resultados notables a largo plazo. Se apoya en principios de psicología del comportamiento para diseñar sistemas que faciliten hábitos positivos.',
 160.00, 22, 'activo'),

(6, 'El nombre del viento', 'Patrick Rothfuss', 'DAW Books',
 'Primer libro de la saga Crónica del Asesino de Reyes.',
 'Kvothe, un legendario mago convertido en posadero, relata en primera persona su ascenso desde la pobreza hasta convertirse en una figura mítica. Combina magia, música y aventura en un mundo profundamente construido.',
 190.00, 10, 'activo'),

(6, 'El Hobbit', 'J. R. R. Tolkien', 'George Allen & Unwin',
 'Aventura previa a El Señor de los Anillos.',
 'Bilbo Bolsón, un hobbit tranquilo, es reclutado por el mago Gandalf y un grupo de enanos para recuperar un tesoro custodiado por el dragón Smaug. Es la puerta de entrada a la Tierra Media y al universo creado por Tolkien.',
 175.00, 15, 'activo'),

(6, 'Harry Potter y la piedra filosofal', 'J. K. Rowling', 'Bloomsbury',
 'Primer libro de la saga sobre el joven mago Harry Potter.',
 'Harry descubre en su undécimo cumpleaños que es un mago y es enviado al Colegio Hogwarts, donde enfrenta el misterio de la piedra filosofal y las primeras señales del regreso de un poderoso enemigo. Da inicio a una de las sagas juveniles más populares.',
 165.00, 30, 'activo'),

(7, 'It (Eso)', 'Stephen King', 'Viking Press',
 'Terror clásico ambientado en el pueblo de Derry.',
 'Un grupo de amigos enfrenta en su niñez a una entidad maligna que adopta la forma de un payaso, y debe reunirse de adultos para derrotarla definitivamente. Combina terror sobrenatural con una profunda exploración de los traumas de la infancia.',
 200.00, 9, 'activo'),

(7, 'El resplandor', 'Stephen King', 'Doubleday',
 'Terror psicológico en un hotel aislado.',
 'Jack Torrance acepta cuidar un hotel aislado durante el invierno junto a su familia, mientras fuerzas sobrenaturales y su propia inestabilidad mental lo empujan hacia la locura. Es una de las obras más influyentes del terror psicológico.',
 170.00, 8, 'activo'),

(7, 'Diez negritos', 'Agatha Christie', 'Collins Crime Club',
 'Misterio clásico sobre diez personas atrapadas en una isla.',
 'Diez desconocidos son invitados a una isla aislada donde, uno a uno, comienzan a morir siguiendo el patrón de una siniestra rima infantil. Es considerada una obra maestra del género policial por su ingenioso planteamiento.',
 140.00, 11, 'activo'),

(8, 'Veinte poemas de amor y una canción desesperada', 'Pablo Neruda', 'Nascimento',
 'Poemario emblemático de la lírica en español.',
 'Reúne poemas de juventud que exploran el amor, el deseo y la melancolía con un lenguaje sensorial e intenso. Es una de las obras poéticas más leídas y traducidas de la literatura en español.',
 95.00, 14, 'activo'),

(8, 'Hojas de hierba', 'Walt Whitman', 'Rome Brothers',
 'Colección poética fundamental de la literatura estadounidense.',
 'Celebra la naturaleza, la democracia y el cuerpo humano con un estilo de verso libre innovador para su época. Whitman revisó y amplió la obra a lo largo de toda su vida, convirtiéndola en un proyecto poético en constante evolución.',
 105.00, 6, 'activo'),

(9, 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster',
 'Biografía autorizada del cofundador de Apple.',
 'Basada en entrevistas exclusivas con Jobs y las personas de su entorno, recorre su vida personal y profesional, desde la fundación de Apple hasta su legado en el diseño de tecnología. Ofrece una mirada honesta a sus virtudes y contradicciones como líder.',
 185.00, 12, 'activo'),

(9, 'Los diarios de Che Guevara', 'Ernesto Che Guevara', 'Ocean Press',
 'Relatos y reflexiones personales del revolucionario.',
 'Recopila anotaciones personales escritas durante distintos momentos de su vida, incluyendo su etapa como guerrillero. Ofrece una perspectiva íntima sobre su pensamiento político e ideológico.',
 150.00, 7, 'activo'),

(10, 'Padre rico, padre pobre', 'Robert Kiyosaki', 'Warner Books',
 'Educación financiera a través de dos figuras paternas.',
 'Contrasta las enseñanzas financieras de dos figuras paternas con visiones opuestas sobre el dinero, el trabajo y la inversión. Busca cambiar la mentalidad del lector frente a los activos, los pasivos y la independencia financiera.',
 145.00, 19, 'activo'),

(10, 'El hombre más rico de Babilonia', 'George S. Clason', 'BN Publishing',
 'Lecciones financieras narradas como parábolas.',
 'A través de relatos ambientados en la antigua Babilonia, expone principios básicos de ahorro, inversión y manejo del dinero. Su formato narrativo lo ha convertido en un clásico introductorio a la educación financiera.',
 120.00, 21, 'activo'),

(11, 'Don Quijote de la Mancha', 'Miguel de Cervantes', 'Francisco de Robles',
 'Obra cumbre de la literatura española.',
 'Narra las aventuras de un hidalgo que, enloquecido por las novelas de caballería, decide convertirse en caballero andante junto a su fiel escudero Sancho Panza. Es considerada la primera novela moderna y una de las obras más influyentes de la literatura universal.',
 210.00, 10, 'activo'),

(12, 'Bajo la misma estrella', 'John Green', 'Dutton Books',
 'Historia de amor entre dos jóvenes con cáncer.',
 'Hazel y Augustus se conocen en un grupo de apoyo para pacientes con cáncer y desarrollan una historia de amor marcada por el humor, la fragilidad y la búsqueda de sentido ante la enfermedad. Es una de las novelas juveniles contemporáneas más leídas.',
 135.00, 16, 'activo');