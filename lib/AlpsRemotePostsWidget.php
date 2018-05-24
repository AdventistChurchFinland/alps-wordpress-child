<?php

/**
 * Adds Alps_Remote_Posts_Widget widget.
 */
class Alps_Remote_Posts_Widget extends WP_Widget {
 
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'remote_posts_widget', // Base ID
            'Remote Posts Widget', // Name
            array( 'description' => __( 'A Remote Posts Widget that fetches posts from another WordPress site', 'text_domain' ), ) // Args
        );
    }
 
    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        
        if ( empty( $instance['url'] ) ) {
            return null;
        }

        $show_in_sidebar = (int) $instance['show_in_sidebar'];

        if ( $show_in_sidebar ) {
            echo '<div class="spacing">';
        } else {
            echo '<hr class="w--100p">';
            echo '<div class="pad--primary spacing--half">';
        }


        $title = apply_filters( 'widget_title', $instance['title'] );
 
        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title;
            if ( $show_in_sidebar ) {
                echo '<h3 class="font--tertiary--m theme--secondary-text-color">' . $title . '</h3>';
            } else {
                echo '<h2 class="font--tertiary--l theme--primary-text-color">' . $title . '</h2>';
            }
            echo $after_title;
        }

        $num_posts = 3;
        if ( ! empty( $instance['num_posts'] ) ) {
            $num_posts = $instance['num_posts'];
        }

        $url = $instance['url'] . '/wp-json/wp/v2/posts?per_page=' . $num_posts;
        if ( ! empty( $instance['categories'] ) ) {
            $url = $url . '&categories=' . $instance['categories'];
        }

        $request = wp_remote_get( $url );
        if( is_wp_error( $request ) ) {
            return false;
        }
        $body = wp_remote_retrieve_body( $request );
        $posts = json_decode( $body );

        if( ! empty( $posts ) ) {
            
            foreach( $posts as $post ) {
                $img_url = null;

                if( $post->featured_media ) {
                    $media_request = wp_remote_get( $instance['url'] . '/wp-json/wp/v2/media/' . $post->featured_media );
                    if( !is_wp_error( $media_request ) ) {
                        $media_body = wp_remote_retrieve_body( $media_request );
                        $media = json_decode( $media_body );
                        if( ! empty( $media ) ) {
                            $img_url = str_replace('http://', 'https://', $media->source_url);
                            $imgSizeDefinition = 'medium';
                            if ( ! empty( $instance['thumbnail_size'] ) ) {
                                $imgSizeDefinition = $instance['thumbnail_size'];
                            }
                            if ( !empty( $media->media_details->sizes->{$imgSizeDefinition} )) {
                                $img_url = str_replace('http://', 'https://', $media->media_details->sizes->{$imgSizeDefinition}->source_url);
                            }
                        }
                    }
                }

                $desc = @html_entity_decode( $post->content->rendered, ENT_QUOTES, get_option( 'blog_charset' ) );
                $desc = esc_attr( wp_trim_words( $desc, 20, ' [&hellip;]' ) );
  
                if ( $show_in_sidebar ) {
                    echo '<div class="content__block">';
                        echo '<h3 class="theme--primary-text-color font--secondary--m"><a href="' . esc_url( $post->link ) . '" target="_blank">' . $post->title->rendered . '</a></h3>';
                        //echo '<p>Proin dictum lobortis luctus. Sed sagittis massa id blandit aliquet.  <a href="' . esc_url( $post->link ) . '" class="font--secondary--s upper theme--secondary-text-color"><strong>Vivamus orci magna</strong></a> </p>';
                    echo '</div>';
                    echo '<hr>';
                } else {
                    echo '<div class="media-block block">';
                        echo '<div class="media-block__inner block__row">';
                            if( $img_url ) {
                                echo '<a class="media-block__image-wrap block__image-wrap db" target="_blank" href="' . esc_url( $post->link ) . '">';
                                    echo '<div class=" dib"><img class="media-block__image block__image" src="' . esc_url( $img_url ) . '" alt="' . $post->title->rendered . '"></div>';
                                echo '</a>';
                            }
                            echo '<div class="media-block__content block__content ">';
                                echo '<h3 class="media-block__title block__title"><a href="' . esc_url( $post->link ) . '" target="_blank" class="block__title-link theme--primary-text-color">' . $post->title->rendered . '</a></h3>';
                                echo '<div class="spacing--half">';
                                    echo '<div class="text text--s pad-half--btm"><p class="media-block__description block__description"><span class="font--primary--xs">' . strip_tags( $desc ) . '</span></p></div>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                }

            }

        }    

        echo '</div>';
        echo $after_widget;
    }
 
    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'New title', 'text_domain' );
        $url = isset( $instance[ 'url' ] ) ? $instance[ 'url' ] : null;
        $num_posts = isset( $instance[ 'num_posts' ] ) ? $instance[ 'num_posts' ] : 3;
        $categories = isset( $instance[ 'categories' ] ) ? $instance[ 'categories' ] : null;
        $thumbnail_size = isset( $instance[ 'thumbnail_size' ] ) ? $instance[ 'thumbnail_size' ] : 'medium';
        $show_in_sidebar = isset( $instance['show_in_sidebar'] ) ? (int) $instance['show_in_sidebar'] : 0;

        ?>
        <p>
        <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_name( 'url' ); ?>"><?php _e( 'Site Url (no trailing slash):' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo $url; ?>" />
        </p>
        <p><label for="<?php echo $this->get_field_name( 'num_posts' ); ?>"><?php _e( 'How many items would you like to display?' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>">
        <?php
        for ( $i = 1; $i <= 20; ++$i ) {
            echo "<option value='$i' " . selected( $num_posts, $i, false ) . ">$i</option>";
        }
        ?>
        </select></p>        
        <p>
        <label for="<?php echo $this->get_field_name( 'categories' ); ?>"><?php _e( 'Categories:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'categories' ); ?>" name="<?php echo $this->get_field_name( 'categories' ); ?>" type="text" value="<?php echo $categories; ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_name( 'thumbnail_size' ); ?>"><?php _e( 'Thumbnail size (name):' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'thumbnail_size' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ); ?>" type="text" value="<?php echo $thumbnail_size; ?>" />
        </p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_in_sidebar' ); ?>" name="<?php echo $this->get_field_name( 'show_in_sidebar' ); ?>" type="checkbox" value="1" <?php checked( $show_in_sidebar ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_in_sidebar' ); ?>"><?php _e( 'Show in sidebar?' ); ?></label>
        </p>

        <?php
    }
 
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['url'] = ( !empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';
        $instance['num_posts'] = ( !empty( $new_instance['num_posts'] ) ) ? strip_tags( $new_instance['num_posts'] ) : '';
        $instance['categories'] = ( !empty( $new_instance['categories'] ) ) ? strip_tags( $new_instance['categories'] ) : '';
        $instance['thumbnail_size'] = ( !empty( $new_instance['thumbnail_size'] ) ) ? strip_tags( $new_instance['thumbnail_size'] ) : '';
        $instance['show_in_sidebar'] = isset( $new_instance['show_in_sidebar'] ) ? (int) $new_instance['show_in_sidebar'] : 0;
 
        return $instance;
    }
 
} // class Alps_Remote_Posts_Widget